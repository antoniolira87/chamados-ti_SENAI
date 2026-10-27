<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Prioridade antiga => [impacto, urgência] equivalentes na matriz. */
    private const CONVERSAO = [
        'urgente' => [1, 1],
        'alta' => [1, 2],
        'media' => [2, 2],
        'baixa' => [2, 3],
    ];

    public function up(): void
    {
        Schema::table('chamados', function (Blueprint $table) {
            // Número do incidente no formato INC0010001.
            $table->string('numero', 12)->nullable()->unique()->after('id');
            // 1 = Alto, 2 = Médio, 3 = Baixo
            $table->unsignedTinyInteger('impacto')->default(3)->after('descricao');
            $table->unsignedTinyInteger('urgencia')->default(3)->after('impacto');
            $table->foreignId('grupo_id')->nullable()->after('categoria_id')->constrained('grupos')->nullOnDelete();
        });

        // Converte os chamados existentes para o novo modelo.
        foreach (DB::table('chamados')->get(['id', 'prioridade']) as $chamado) {
            [$impacto, $urgencia] = self::CONVERSAO[$chamado->prioridade] ?? [3, 3];

            DB::table('chamados')->where('id', $chamado->id)->update([
                'numero' => 'INC'.str_pad((string) (10000 + $chamado->id), 7, '0', STR_PAD_LEFT),
                'impacto' => $impacto,
                'urgencia' => $urgencia,
                'prioridade' => (string) ($impacto + $urgencia - 1),
            ]);
        }

        // Prioridade passa a ser numérica: 1 (Crítica) a 5 (Planejamento).
        Schema::table('chamados', function (Blueprint $table) {
            $table->unsignedTinyInteger('prioridade')->default(5)->change();
        });

        Schema::table('chamado_historicos', function (Blueprint $table) {
            // comentario = visível ao solicitante | nota = nota de trabalho interna
            $table->string('tipo', 20)->default('comentario')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('chamado_historicos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        Schema::table('chamados', function (Blueprint $table) {
            $table->string('prioridade', 20)->default('media')->change();
        });

        $antigas = ['1' => 'urgente', '2' => 'alta', '3' => 'media', '4' => 'baixa', '5' => 'baixa'];
        foreach ($antigas as $nova => $antiga) {
            DB::table('chamados')->where('prioridade', $nova)->update(['prioridade' => $antiga]);
        }

        Schema::table('chamados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grupo_id');
            $table->dropUnique(['numero']);
            $table->dropColumn(['numero', 'impacto', 'urgencia']);
        });
    }
};
