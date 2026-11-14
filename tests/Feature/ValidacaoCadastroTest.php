<?php

namespace Tests\Feature;

use App\Models\User;
use App\Rules\Cpf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Manutenção "Validação de dados cadastrais".
 * Testa o formulário de usuários com valores válidos, inválidos e situações-limite.
 * A data de hoje fica fixa em 10/11/2026 para os testes de idade não mudarem com o tempo.
 */
class ValidacaoCadastroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-10 10:00:00');
    }

    private function dadosValidos(array $troca = []): array
    {
        return array_merge([
            'name' => 'Fulano de Tal',
            'email' => 'fulano@empresa.com',
            'data_nascimento' => '1990-05-10',
            'cpf' => '529.982.247-25',
            'celular' => '(11) 98765-4321',
            'cep' => '01310-100',
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
            'cpf' => '52998224725',
            'celular' => '11987654321',
            'cep' => '01310100',
        ]);
        $this->assertSame('1990-05-10', User::firstWhere('email', 'fulano@empresa.com')->data_nascimento->format('Y-m-d'));
    }

    public function test_aceita_cpf_cep_e_celular_sem_mascara(): void
    {
        $this->post('/cadastro', $this->dadosValidos([
            'cpf' => '52998224725', 'celular' => '11987654321', 'cep' => '01310100',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['cpf' => '52998224725']);
    }

    public static function limitesValidos(): array
    {
        return [
            'nome com 3 letras' => ['name', 'Ana'],
            'nome com 100 caracteres' => ['name', str_repeat('a', 100)],
            'faz 18 anos hoje' => ['data_nascimento', '2008-11-10'],
            'tem exatamente 100 anos' => ['data_nascimento', '1926-11-10'],
            'nascido em 29/02 (ano bissexto)' => ['data_nascimento', '2000-02-29'],
            'CPF com dígito verificador 0' => ['cpf', '111.444.777-35'],
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
            // Data de nascimento
            'nascimento vazio' => ['data_nascimento', ''],
            'data inexistente 31/02' => ['data_nascimento', '2000-02-31'],
            '29/02 em ano não bissexto' => ['data_nascimento', '2001-02-29'],
            'data no formato errado' => ['data_nascimento', '31/12/2000'],
            'data que não é data' => ['data_nascimento', 'abc'],
            'data no futuro' => ['data_nascimento', '2027-01-01'],
            'faz 18 anos amanhã (17 anos)' => ['data_nascimento', '2008-11-11'],
            'faz 101 anos hoje' => ['data_nascimento', '1925-11-10'],
            // CPF
            'CPF vazio' => ['cpf', ''],
            'CPF com dígito errado' => ['cpf', '529.982.247-26'],
            'CPF todo repetido' => ['cpf', '111.111.111-11'],
            'CPF de zeros' => ['cpf', '00000000000'],
            'CPF com 10 números' => ['cpf', '5299822472'],
            'CPF com 12 números' => ['cpf', '529982247251'],
            'CPF com letra' => ['cpf', '529.982.247-2A'],
            // CEP
            'CEP vazio' => ['cep', ''],
            'CEP com 7 números' => ['cep', '1234567'],
            'CEP com hífen no lugar errado' => ['cep', '1234-5678'],
            'CEP com letra' => ['cep', '12345-67a'],
            'CEP com ponto' => ['cep', '12.345-678'],
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
            ['cpf', '529.982.247-26', 'O CPF informado é inválido. Confira os números digitados.'],
            ['cpf', '123', 'O CPF deve ter exatamente 11 números (pode digitar com ou sem pontos e traço).'],
            ['data_nascimento', '2027-01-01', 'A data de nascimento não pode estar no futuro.'],
            ['data_nascimento', '2010-01-01', 'É preciso ter pelo menos 18 anos completos.'],
            ['data_nascimento', '2000-02-31', 'Informe uma data de nascimento que exista no calendário (dia/mês/ano).'],
            ['celular', '11887654321', 'O número do celular deve começar com 9 logo depois do DDD.'],
            ['cep', '1234-5678', 'O CEP deve estar no formato 12345-678 ou 12345678, somente com números.'],
            ['name', 'Al', 'O nome deve ter pelo menos 3 letras.'],
        ];

        foreach ($casos as [$campo, $valor, $mensagem]) {
            $this->post('/cadastro', $this->dadosValidos([$campo => $valor]))
                ->assertSessionHasErrors([$campo => $mensagem]);
        }
    }

    // ---------- Inclusão e alteração pelo administrador ----------

    public function test_cpf_repetido_e_recusado(): void
    {
        User::factory()->create(['cpf' => '52998224725']);

        $this->post('/cadastro', $this->dadosValidos())
            ->assertSessionHasErrors(['cpf' => 'Já existe um usuário cadastrado com este CPF.']);
    }

    public function test_admin_inclusao_tambem_valida(): void
    {
        $admin = User::factory()->create(['perfil' => 'admin']);

        $this->actingAs($admin)
            ->post('/usuarios', $this->dadosValidos(['perfil' => 'tecnico', 'cpf' => '111.111.111-11']))
            ->assertSessionHasErrors('cpf');
        $this->assertDatabaseMissing('users', ['email' => 'fulano@empresa.com']);

        $this->actingAs($admin)
            ->post('/usuarios', $this->dadosValidos(['perfil' => 'tecnico']))
            ->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'fulano@empresa.com', 'perfil' => 'tecnico', 'cpf' => '52998224725']);
    }

    public function test_admin_alteracao_tambem_valida(): void
    {
        $admin = User::factory()->create(['perfil' => 'admin']);
        $maria = User::factory()->create(['email' => 'maria@empresa.com', 'cpf' => '52998224725']);
        $dados = fn (array $troca) => $this->dadosValidos(['email' => 'maria@empresa.com', 'perfil' => 'usuario', 'password' => '', 'password_confirmation' => ''] + $troca);

        // Inválido: nada muda no banco.
        $this->actingAs($admin)->put("/usuarios/{$maria->id}", $dados(['data_nascimento' => '2015-03-01', 'cep' => 'abc']))
            ->assertSessionHasErrors(['data_nascimento', 'cep']);
        $this->assertNotEquals('2015-03-01', $maria->fresh()->data_nascimento->format('Y-m-d'));

        // Válido: mantém o próprio CPF (não acusa duplicado) e grava os números.
        $this->actingAs($admin)->put("/usuarios/{$maria->id}", $dados(['cpf' => '529.982.247-25', 'cep' => '04538-133']))
            ->assertRedirect(route('usuarios.index'))->assertSessionHasNoErrors();
        $this->assertSame('04538133', $maria->fresh()->cep);
    }

    public function test_alteracao_nao_aceita_cpf_de_outro_usuario(): void
    {
        $admin = User::factory()->create(['perfil' => 'admin']);
        User::factory()->create(['cpf' => '11144477735']);
        $maria = User::factory()->create(['email' => 'maria@empresa.com']);

        $this->actingAs($admin)
            ->put("/usuarios/{$maria->id}", $this->dadosValidos(['email' => 'maria@empresa.com', 'perfil' => 'usuario', 'cpf' => '111.444.777-35', 'password' => '', 'password_confirmation' => '']))
            ->assertSessionHasErrors('cpf');
    }

    // ---------- Regra do CPF isolada ----------

    public function test_regra_do_cpf(): void
    {
        $this->assertTrue(Cpf::valido('52998224725'));
        $this->assertTrue(Cpf::valido('11144477735'));
        $this->assertFalse(Cpf::valido('52998224724'));
        $this->assertFalse(Cpf::valido('99999999999'));
        $this->assertTrue(Cpf::valido(Cpf::gerar()));
        $this->assertSame('529.982.247-25', Cpf::formatar('52998224725'));
    }
}
