<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Chamado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Manutenção "Validação de dados cadastrais" aplicada ao formulário de incidentes:
 * texto obrigatório (título e descrição) e período (problema começou em / prazo para resolver).
 * A data de hoje fica fixa em 10/11/2026.
 */
class ValidacaoChamadoTest extends TestCase
{
    use RefreshDatabase;

    private User $solicitante;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-10 10:00:00');
        $this->solicitante = User::factory()->create(['perfil' => 'usuario']);
        $this->categoria = Categoria::create(['nome' => 'Hardware']);
    }

    private function dadosValidos(array $troca = []): array
    {
        return array_merge([
            'titulo' => 'Impressora não imprime',
            'descricao' => 'A impressora do 2º andar não imprime nada desde ontem.',
            'categoria_id' => $this->categoria->id,
            'impacto' => 3,
            'urgencia' => 3,
            'inicio_problema' => '2026-11-09',
            'prazo' => '2026-11-12',
        ], $troca);
    }

    private function registrar(array $dados)
    {
        return $this->actingAs($this->solicitante)->from('/chamados/create')->post('/chamados', $dados);
    }

    // ---------- Valores válidos ----------

    public function test_incidente_valido_e_gravado_com_texto_normalizado(): void
    {
        $this->registrar($this->dadosValidos([
            'titulo' => '   Impressora    não   imprime  ',
            'descricao' => "\n  Não imprime.\nJá reiniciei.  \n",
        ]))->assertSessionHasNoErrors();

        $chamado = Chamado::first();
        $this->assertSame('Impressora não imprime', $chamado->titulo);
        $this->assertSame("Não imprime.\nJá reiniciei.", $chamado->descricao);
        $this->assertSame('2026-11-09', $chamado->inicio_problema->format('Y-m-d'));
        $this->assertSame('2026-11-12', $chamado->prazo->format('Y-m-d'));
    }

    public static function limitesValidos(): array
    {
        return [
            'título com 3 caracteres' => [['titulo' => 'VPN']],
            'título com 100 caracteres' => [['titulo' => str_repeat('a', 100)]],
            'descrição com 10 caracteres' => [['descricao' => '1234567890']],
            'começou hoje' => [['inicio_problema' => '2026-11-10', 'prazo' => '2026-11-15']],
            'período de um único dia (início = prazo)' => [['inicio_problema' => '2026-11-10', 'prazo' => '2026-11-10']],
            'começou há meses e prazo hoje' => [['inicio_problema' => '2026-06-01', 'prazo' => '2026-11-10']],
            '29/02 em ano bissexto' => [['inicio_problema' => '2024-02-29']],
        ];
    }

    #[DataProvider('limitesValidos')]
    public function test_situacoes_limite_aceitas(array $troca): void
    {
        $this->registrar($this->dadosValidos($troca))->assertSessionHasNoErrors();

        $this->assertSame(1, Chamado::count());
    }

    // ---------- Valores inválidos ----------

    public static function valoresInvalidos(): array
    {
        return [
            'título vazio' => ['titulo', ['titulo' => '']],
            'título só com espaços' => ['titulo', ['titulo' => '      ']],
            'título com 2 caracteres' => ['titulo', ['titulo' => 'PC']],
            'título com 101 caracteres' => ['titulo', ['titulo' => str_repeat('a', 101)]],
            'descrição vazia' => ['descricao', ['descricao' => '']],
            'descrição só com espaços' => ['descricao', ['descricao' => "   \n   "]],
            'descrição com 9 caracteres' => ['descricao', ['descricao' => '123456789']],
            'descrição com 5001 caracteres' => ['descricao', ['descricao' => str_repeat('a', 5001)]],
            'início vazio' => ['inicio_problema', ['inicio_problema' => '']],
            'início inexistente 30/02' => ['inicio_problema', ['inicio_problema' => '2026-02-30']],
            'início que não é data' => ['inicio_problema', ['inicio_problema' => 'ontem']],
            'início no futuro' => ['inicio_problema', ['inicio_problema' => '2026-11-11', 'prazo' => '2026-11-20']],
            'prazo vazio' => ['prazo', ['prazo' => '']],
            'prazo inexistente 31/11' => ['prazo', ['prazo' => '2026-11-31']],
            'prazo antes do início' => ['prazo', ['inicio_problema' => '2026-11-09', 'prazo' => '2026-11-08']],
            'prazo que já passou' => ['prazo', ['inicio_problema' => '2026-11-01', 'prazo' => '2026-11-05']],
        ];
    }

    #[DataProvider('valoresInvalidos')]
    public function test_valor_invalido_e_recusado_e_nada_e_gravado(string $campo, array $troca): void
    {
        $this->registrar($this->dadosValidos($troca))
            ->assertRedirect('/chamados/create')
            ->assertSessionHasErrors($campo);

        $this->assertSame(0, Chamado::count());
    }

    public function test_mensagens_especificas_para_cada_erro(): void
    {
        $casos = [
            ['titulo', ['titulo' => 'PC'], 'A descrição resumida deve ter pelo menos 3 caracteres.'],
            ['descricao', ['descricao' => 'curto'], 'Explique melhor o problema: escreva pelo menos 10 caracteres.'],
            ['inicio_problema', ['inicio_problema' => '2026-11-11'], 'O problema não pode ter começado numa data futura.'],
            ['inicio_problema', ['inicio_problema' => '2026-02-30'], 'Informe uma data de início que exista no calendário (dia/mês/ano).'],
            ['prazo', ['prazo' => '2026-11-08'], 'Período inconsistente: o prazo não pode ser anterior à data em que o problema começou.'],
            ['prazo', ['inicio_problema' => '2026-11-01', 'prazo' => '2026-11-05'], 'O prazo para resolver não pode ser uma data que já passou.'],
        ];

        foreach ($casos as [$campo, $troca, $mensagem]) {
            $this->registrar($this->dadosValidos($troca))->assertSessionHasErrors([$campo => $mensagem]);
        }
    }

    // ---------- Alteração ----------

    public function test_alteracao_tambem_valida_o_periodo(): void
    {
        $chamado = Chamado::create($this->dadosValidos() + ['solicitante_id' => $this->solicitante->id, 'status' => 'aberto']);

        $this->actingAs($this->solicitante)
            ->put("/chamados/{$chamado->id}", $this->dadosValidos(['inicio_problema' => '2026-11-09', 'prazo' => '2026-11-01', 'titulo' => 'ab']))
            ->assertSessionHasErrors(['prazo', 'titulo']);
        $this->assertSame('2026-11-12', $chamado->fresh()->prazo->format('Y-m-d'));
    }

    public function test_alteracao_de_incidente_antigo_aceita_prazo_ja_vencido(): void
    {
        // Um incidente antigo pode estar atrasado; corrigir outro campo não deve travar a edição.
        $chamado = Chamado::create($this->dadosValidos(['inicio_problema' => '2026-10-01', 'prazo' => '2026-10-05'])
            + ['solicitante_id' => $this->solicitante->id, 'status' => 'aberto']);

        $this->actingAs($this->solicitante)
            ->put("/chamados/{$chamado->id}", $this->dadosValidos(['inicio_problema' => '2026-10-01', 'prazo' => '2026-10-05', 'titulo' => 'Título corrigido']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Título corrigido', $chamado->fresh()->titulo);
        $this->assertTrue($chamado->fresh()->atrasado());
    }

    public function test_incidente_dentro_do_prazo_nao_esta_atrasado(): void
    {
        $chamado = Chamado::create($this->dadosValidos() + ['solicitante_id' => $this->solicitante->id, 'status' => 'aberto']);

        $this->assertFalse($chamado->atrasado());
        $this->actingAs($this->solicitante)->get("/chamados/{$chamado->id}")
            ->assertOk()->assertSee('09/11/2026 até 12/11/2026')->assertDontSee('Atrasado');
    }
}
