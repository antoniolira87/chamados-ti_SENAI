<?php

use App\Models\Chamado;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chamados', function (Blueprint $table) {
            // Período do incidente: quando o problema começou e até quando deve ser resolvido.
            $table->date('inicio_problema')->nullable()->after('descricao');
            $table->date('prazo')->nullable()->after('inicio_problema');
        });

        // Incidentes que já existiam: início = data de abertura; prazo pela prioridade.
        foreach (DB::table('chamados')->get(['id', 'created_at', 'prioridade']) as $chamado) {
            $inicio = Carbon::parse($chamado->created_at)->startOfDay();
            DB::table('chamados')->where('id', $chamado->id)->update([
                'inicio_problema' => $inicio->toDateString(),
                'prazo' => $inicio->addDays(Chamado::DIAS_DE_PRAZO[$chamado->prioridade] ?? 5)->toDateString(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('chamados', function (Blueprint $table) {
            $table->dropColumn(['inicio_problema', 'prazo']);
        });
    }
};
