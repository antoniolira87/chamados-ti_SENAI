<?php

namespace App\Http\Controllers;

use App\Models\Chamado;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $base = Chamado::query()->visivelPara($user);

        $porStatus = (clone $base)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $porPrioridade = (clone $base)
            ->pendentes()
            ->selectRaw('prioridade, count(*) as total')
            ->groupBy('prioridade')
            ->pluck('total', 'prioridade');

        $recentes = (clone $base)
            ->with(['categoria', 'grupo', 'tecnico'])
            ->latest()
            ->limit(6)
            ->get();

        $meusAtendimentos = $user->podeAtender()
            ? Chamado::with('categoria')
                ->where('tecnico_id', $user->id)
                ->pendentes()
                ->orderBy('prioridade')
                ->limit(5)
                ->get()
            : collect();

        // Indicadores (KPIs) do topo do painel.
        $indicadores = [
            'pendentes' => (clone $base)->pendentes()->count(),
            'criticos' => (clone $base)->pendentes()->where('prioridade', '<=', 2)->count(),
            'sem_atribuicao' => (clone $base)->pendentes()->whereNull('tecnico_id')->count(),
            'meus' => $user->podeAtender()
                ? Chamado::where('tecnico_id', $user->id)->pendentes()->count()
                : (clone $base)->where('status', 'resolvido')->count(),
        ];

        $total = $porStatus->sum();

        return view('dashboard', compact('porStatus', 'porPrioridade', 'recentes', 'meusAtendimentos', 'indicadores', 'total'));
    }
}
