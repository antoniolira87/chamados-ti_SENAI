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
            ->whereNotIn('status', ['resolvido', 'fechado'])
            ->selectRaw('prioridade, count(*) as total')
            ->groupBy('prioridade')
            ->pluck('total', 'prioridade');

        $recentes = (clone $base)
            ->with(['categoria', 'tecnico'])
            ->latest()
            ->limit(5)
            ->get();

        $meusAtendimentos = $user->podeAtender()
            ? Chamado::with('categoria')
                ->where('tecnico_id', $user->id)
                ->whereNotIn('status', ['resolvido', 'fechado'])
                ->orderByRaw("case prioridade when 'urgente' then 1 when 'alta' then 2 when 'media' then 3 else 4 end")
                ->limit(5)
                ->get()
            : collect();

        $total = $porStatus->sum();

        return view('dashboard', compact('porStatus', 'porPrioridade', 'recentes', 'meusAtendimentos', 'total'));
    }
}
