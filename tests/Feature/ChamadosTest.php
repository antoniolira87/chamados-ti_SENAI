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
            'impacto' => 1,
            'urgencia' => 2,
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
            'impacto' => 2,
            'urgencia' => 1,
        ])->assertRedirect();

        $chamado = Chamado::first();
        $this->assertSame('aberto', $chamado->status);
        $this->assertSame($user->id, $chamado->solicitante_id);
        $this->assertSame(2, $chamado->prioridade);
        $this->assertMatchesRegularExpression('/^INC\d{7}$/', $chamado->numero);
        $this->assertCount(1, $chamado->historicos);
    }

    public function test_validacao_ao_abrir_chamado(): void
    {
        $this->actingAs($this->usuario())
            ->post('/chamados', ['titulo' => '', 'impacto' => 9])
            ->assertSessionHasErrors(['titulo', 'descricao', 'categoria_id', 'impacto', 'urgencia']);
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

        $this->actingAs($this->usuario('tecnico'))->get('/grupos')->assertForbidden();
        $this->actingAs($admin)->post('/grupos', ['nome' => 'Banco de Dados'])->assertRedirect(route('grupos.index'));
        $this->assertDatabaseHas('grupos', ['nome' => 'Banco de Dados']);
    }

    public function test_prioridade_segue_matriz_impacto_urgencia(): void
    {
        $matriz = [[1, 1, 1], [1, 2, 2], [1, 3, 3], [2, 2, 3], [2, 3, 4], [3, 2, 4], [3, 3, 5]];

        foreach ($matriz as [$impacto, $urgencia, $esperada]) {
            $chamado = $this->chamado($this->usuario(), compact('impacto', 'urgencia'));
            $this->assertSame($esperada, $chamado->prioridade, "Impacto {$impacto} x Urgência {$urgencia}");
        }
    }

    public function test_numero_do_incidente_e_sequencial(): void
    {
        $user = $this->usuario();
        $a = $this->chamado($user);
        $b = $this->chamado($user);

        $this->assertSame(Chamado::gerarNumero($a->id), $a->fresh()->numero);
        $this->assertSame('INC'.str_pad((string) (10000 + $b->id), 7, '0', STR_PAD_LEFT), $b->fresh()->numero);
        $this->actingAs($user)->get('/chamados?busca='.$b->numero)->assertSee($b->titulo);
    }

    public function test_nota_de_trabalho_fica_oculta_para_o_solicitante(): void
    {
        $tecnico = $this->usuario('tecnico');
        $solicitante = $this->usuario();
        $chamado = $this->chamado($solicitante);

        $this->actingAs($tecnico)->patch(route('chamados.status', $chamado), [
            'status' => 'em_andamento', 'tecnico_id' => $tecnico->id,
            'tipo' => 'nota', 'comentario' => 'Senha do roteador é segredo-interno',
        ]);
        $this->actingAs($tecnico)->patch(route('chamados.status', $chamado), [
            'status' => 'em_andamento', 'tecnico_id' => $tecnico->id,
            'tipo' => 'comentario', 'comentario' => 'Estou indo até sua mesa.',
        ]);

        $this->actingAs($tecnico)->get(route('chamados.show', $chamado))
            ->assertSee('segredo-interno')->assertSee('Estou indo até sua mesa.');

        // O solicitante vê a mudança de estado e o comentário, mas não a nota interna.
        $this->actingAs($solicitante)->get(route('chamados.show', $chamado))
            ->assertDontSee('segredo-interno')
            ->assertSee('Estou indo até sua mesa.')
            ->assertSee('Em andamento');
    }

    public function test_resolver_exige_notas_de_resolucao(): void
    {
        $tecnico = $this->usuario('tecnico');
        $chamado = $this->chamado($this->usuario());

        $this->actingAs($tecnico)->patch(route('chamados.status', $chamado), ['status' => 'resolvido'])
            ->assertSessionHasErrors('comentario');

        $this->assertSame('aberto', $chamado->fresh()->status);
    }

    public function test_solicitante_adiciona_comentario(): void
    {
        $solicitante = $this->usuario();
        $chamado = $this->chamado($solicitante);

        $this->actingAs($solicitante)->post(route('chamados.comentar', $chamado), ['comentario' => 'Reiniciei e continua.'])
            ->assertRedirect(route('chamados.show', $chamado));

        $this->assertDatabaseHas('chamado_historicos', [
            'chamado_id' => $chamado->id, 'tipo' => 'comentario', 'comentario' => 'Reiniciei e continua.',
        ]);

        $this->actingAs($this->usuario())->post(route('chamados.comentar', $chamado), ['comentario' => 'x'])
            ->assertForbidden();
    }

    public function test_paginas_principais_renderizam(): void
    {
        $this->seed();
        $admin = User::where('perfil', 'admin')->first();
        $chamado = Chamado::first();

        foreach (['/', '/chamados', '/chamados/create', "/chamados/{$chamado->id}", "/chamados/{$chamado->id}/edit",
                  '/categorias', '/categorias/create', '/grupos', '/grupos/create', '/usuarios', '/usuarios/create', "/usuarios/{$admin->id}", "/usuarios/{$admin->id}/edit"] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        $maria = User::where('email', 'maria@helpdesk.test')->first();
        foreach (['/', '/chamados', '/chamados?fila=pendentes', '/chamados/create', '/chamados/'.$maria->chamadosAbertos()->first()->id] as $url) {
            $this->actingAs($maria)->get($url)->assertOk();
        }
    }
}
