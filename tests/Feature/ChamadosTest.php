<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Chamado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChamadosTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(string $perfil = 'usuario'): User
    {
        return User::factory()->create(['perfil' => $perfil]);
    }

    private function chamado(User $solicitante, array $attrs = []): Chamado
    {
        $categoria = Categoria::firstOrCreate(['nome' => 'Hardware']);

        return Chamado::create($attrs + [
            'titulo' => 'PC não liga',
            'descricao' => 'Não liga desde ontem.',
            'prioridade' => 'alta',
            'status' => 'aberto',
            'categoria_id' => $categoria->id,
            'solicitante_id' => $solicitante->id,
        ]);
    }

    public function test_visitante_e_redirecionado_para_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/chamados')->assertRedirect(route('login'));
    }

    public function test_cadastro_e_login(): void
    {
        $this->post('/cadastro', [
            'name' => 'Fulano', 'email' => 'fulano@teste.com',
            'password' => 'segredo1', 'password_confirmation' => 'segredo1',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'fulano@teste.com', 'perfil' => 'usuario']);
        $this->assertAuthenticated();
    }

    public function test_usuario_abre_chamado(): void
    {
        $user = $this->usuario();
        $categoria = Categoria::create(['nome' => 'Software']);

        $this->actingAs($user)->post('/chamados', [
            'titulo' => 'Excel travando',
            'descricao' => 'Trava ao abrir planilhas grandes.',
            'categoria_id' => $categoria->id,
            'prioridade' => 'media',
        ])->assertRedirect();

        $chamado = Chamado::first();
        $this->assertSame('aberto', $chamado->status);
        $this->assertSame($user->id, $chamado->solicitante_id);
        $this->assertCount(1, $chamado->historicos);
    }

    public function test_validacao_ao_abrir_chamado(): void
    {
        $this->actingAs($this->usuario())
            ->post('/chamados', ['titulo' => '', 'prioridade' => 'inexistente'])
            ->assertSessionHasErrors(['titulo', 'descricao', 'categoria_id', 'prioridade']);
    }

    public function test_usuario_ve_apenas_os_proprios_chamados(): void
    {
        $maria = $this->usuario();
        $joao = $this->usuario();
        $chamadoJoao = $this->chamado($joao, ['titulo' => 'Chamado do João']);
        $this->chamado($maria, ['titulo' => 'Chamado da Maria']);

        $this->actingAs($maria)->get('/chamados')
            ->assertSee('Chamado da Maria')
            ->assertDontSee('Chamado do João');

        $this->actingAs($maria)->get(route('chamados.show', $chamadoJoao))->assertForbidden();
    }

    public function test_tecnico_muda_status_e_registra_historico(): void
    {
        $tecnico = $this->usuario('tecnico');
        $chamado = $this->chamado($this->usuario());

        $this->actingAs($tecnico)->patch(route('chamados.status', $chamado), [
            'status' => 'resolvido',
            'tecnico_id' => $tecnico->id,
            'comentario' => 'Fonte trocada.',
        ])->assertRedirect(route('chamados.show', $chamado));

        $chamado->refresh();
        $this->assertSame('resolvido', $chamado->status);
        $this->assertSame($tecnico->id, $chamado->tecnico_id);
        $this->assertNotNull($chamado->resolvido_em);
        $this->assertDatabaseHas('chamado_historicos', [
            'chamado_id' => $chamado->id, 'status_anterior' => 'aberto', 'status_novo' => 'resolvido',
        ]);
    }

    public function test_usuario_comum_nao_muda_status(): void
    {
        $user = $this->usuario();
        $chamado = $this->chamado($user);

        $this->actingAs($user)->patch(route('chamados.status', $chamado), ['status' => 'fechado'])
            ->assertForbidden();
    }

    public function test_solicitante_nao_edita_chamado_em_andamento(): void
    {
        $user = $this->usuario();
        $chamado = $this->chamado($user, ['status' => 'em_andamento']);

        $this->actingAs($user)->get(route('chamados.edit', $chamado))->assertForbidden();
    }

    public function test_filtro_por_status(): void
    {
        $admin = $this->usuario('admin');
        $this->chamado($admin, ['titulo' => 'Chamado aberto X']);
        $this->chamado($admin, ['titulo' => 'Chamado fechado Y', 'status' => 'fechado']);

        $this->actingAs($admin)->get('/chamados?status=fechado')
            ->assertSee('Chamado fechado Y')
            ->assertDontSee('Chamado aberto X');
    }

    public function test_apenas_admin_gerencia_categorias_e_usuarios(): void
    {
        $this->actingAs($this->usuario('tecnico'))->get('/categorias')->assertForbidden();
        $this->actingAs($this->usuario('tecnico'))->get('/usuarios')->assertForbidden();

        $admin = $this->usuario('admin');
        $this->actingAs($admin)->post('/categorias', ['nome' => 'Rede'])->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categorias', ['nome' => 'Rede']);
    }

    public function test_paginas_principais_renderizam(): void
    {
        $this->seed();
        $admin = User::where('perfil', 'admin')->first();
        $chamado = Chamado::first();

        foreach (['/', '/chamados', '/chamados/create', "/chamados/{$chamado->id}", "/chamados/{$chamado->id}/edit",
                  '/categorias', '/categorias/create', '/usuarios', '/usuarios/create', "/usuarios/{$admin->id}", "/usuarios/{$admin->id}/edit"] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
