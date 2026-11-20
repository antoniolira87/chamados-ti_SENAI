<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Validação do formulário de usuários: nome, e-mail e celular,
 * com valores válidos, inválidos e situações-limite.
 */
class ValidacaoCadastroTest extends TestCase
{
    use RefreshDatabase;

    private function dadosValidos(array $troca = []): array
    {
        return array_merge([
            'name' => 'Fulano de Tal',
            'email' => 'fulano@empresa.com',
            'celular' => '(11) 98765-4321',
            'password' => 'segredo1',
            'password_confirmation' => 'segredo1',
        ], $troca);
    }

    // ---------- Valores válidos ----------

    public function test_cadastro_valido_normaliza_e_grava_somente_numeros(): void
    {
        $this->post('/cadastro', $this->dadosValidos([
            'name' => '   Fulano    de   Tal  ',
            'email' => '  FULANO@Empresa.com ',
        ]))->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Fulano de Tal',
            'email' => 'fulano@empresa.com',
            'celular' => '11987654321',
        ]);
    }

    public function test_aceita_celular_sem_mascara(): void
    {
        $this->post('/cadastro', $this->dadosValidos(['celular' => '11987654321']))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['celular' => '11987654321']);
    }

    public static function limitesValidos(): array
    {
        return [
            'nome com 3 letras' => ['name', 'Ana'],
            'nome com 100 caracteres' => ['name', str_repeat('a', 100)],
        ];
    }

    #[DataProvider('limitesValidos')]
    public function test_situacoes_limite_aceitas(string $campo, string $valor): void
    {
        $this->post('/cadastro', $this->dadosValidos([$campo => $valor]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'fulano@empresa.com']);
    }

    // ---------- Valores inválidos ----------

    public static function valoresInvalidos(): array
    {
        return [
            // Nome
            'nome vazio' => ['name', ''],
            'nome só com espaços' => ['name', '     '],
            'nome com 2 letras' => ['name', 'Al'],
            'nome com 101 caracteres' => ['name', str_repeat('a', 101)],
            // E-mail
            'e-mail sem @' => ['email', 'fulano.empresa.com'],
            'e-mail sem domínio' => ['email', 'fulano@'],
            'e-mail sem .com' => ['email', 'fulano@empresa'],
            'e-mail com espaço no meio' => ['email', 'ful ano@empresa.com'],
            // Celular
            'celular vazio' => ['celular', ''],
            'celular sem o 9 (10 números)' => ['celular', '(11) 8765-4321'],
            'celular que não começa com 9' => ['celular', '11887654321'],
            'celular com letra' => ['celular', '(11) 9876a-4321'],
            'celular com 12 números' => ['celular', '119876543210'],
            'celular sem DDD' => ['celular', '987654321'],
        ];
    }

    #[DataProvider('valoresInvalidos')]
    public function test_valor_invalido_e_recusado_e_nada_e_gravado(string $campo, string $valor): void
    {
        $this->from('/cadastro')
            ->post('/cadastro', $this->dadosValidos([$campo => $valor]))
            ->assertRedirect('/cadastro')
            ->assertSessionHasErrors($campo);

        $this->assertDatabaseMissing('users', ['email' => 'fulano@empresa.com']);
        $this->assertGuest();
    }

    // ---------- Mensagens claras ----------

    public function test_mensagens_especificas_para_cada_erro(): void
    {
        $casos = [
            ['celular', '11887654321', 'O número do celular deve começar com 9 logo depois do DDD.'],
            ['name', 'Al', 'O nome deve ter pelo menos 3 letras.'],
        ];

        foreach ($casos as [$campo, $valor, $mensagem]) {
            $this->post('/cadastro', $this->dadosValidos([$campo => $valor]))
                ->assertSessionHasErrors([$campo => $mensagem]);
        }
    }

    // ---------- Inclusão e alteração pelo administrador ----------

    public function test_email_repetido_e_recusado(): void
    {
        User::factory()->create(['email' => 'fulano@empresa.com']);

        $this->post('/cadastro', $this->dadosValidos())->assertSessionHasErrors('email');
    }

    public function test_admin_inclusao_tambem_valida(): void
    {
        $admin = User::factory()->create(['perfil' => 'admin']);

        $this->actingAs($admin)
            ->post('/usuarios', $this->dadosValidos(['perfil' => 'tecnico', 'celular' => '11887654321']))
            ->assertSessionHasErrors('celular');
        $this->assertDatabaseMissing('users', ['email' => 'fulano@empresa.com']);

        $this->actingAs($admin)
            ->post('/usuarios', $this->dadosValidos(['perfil' => 'tecnico']))
            ->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'fulano@empresa.com', 'perfil' => 'tecnico', 'celular' => '11987654321']);
    }

    public function test_admin_alteracao_tambem_valida(): void
    {
        $admin = User::factory()->create(['perfil' => 'admin']);
        $maria = User::factory()->create(['email' => 'maria@empresa.com', 'celular' => '11911112222']);
        $dados = fn (array $troca) => $this->dadosValidos(['email' => 'maria@empresa.com', 'perfil' => 'usuario', 'password' => '', 'password_confirmation' => ''] + $troca);

        // Inválido: nada muda no banco.
        $this->actingAs($admin)->put("/usuarios/{$maria->id}", $dados(['name' => 'Al', 'celular' => 'abc']))
            ->assertSessionHasErrors(['name', 'celular']);
        $this->assertSame('11911112222', $maria->fresh()->celular);

        // Válido: mantém o próprio e-mail (não acusa duplicado) e grava os números.
        $this->actingAs($admin)->put("/usuarios/{$maria->id}", $dados(['celular' => '(21) 99876-5432']))
            ->assertRedirect(route('usuarios.index'))->assertSessionHasNoErrors();
        $this->assertSame('21998765432', $maria->fresh()->celular);
    }
}
