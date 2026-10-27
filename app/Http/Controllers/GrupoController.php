<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GrupoController extends Controller
{
    public function index(): View
    {
        $grupos = Grupo::withCount(['chamados', 'membros'])->orderBy('nome')->paginate(10);

        return view('grupos.index', compact('grupos'));
    }

    public function create(): View
    {
        return view('grupos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:100', 'unique:grupos,nome'],
            'descricao' => ['nullable', 'string', 'max:2000'],
        ]);

        Grupo::create($dados);

        return redirect()->route('grupos.index')->with('sucesso', 'Grupo cadastrado com sucesso.');
    }

    public function show(Grupo $grupo): RedirectResponse
    {
        return redirect()->route('chamados.index', ['grupo_id' => $grupo->id]);
    }

    public function edit(Grupo $grupo): View
    {
        return view('grupos.edit', compact('grupo'));
    }

    public function update(Request $request, Grupo $grupo): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:100', Rule::unique('grupos', 'nome')->ignore($grupo->id)],
            'descricao' => ['nullable', 'string', 'max:2000'],
        ]);

        $grupo->update($dados);

        return redirect()->route('grupos.index')->with('sucesso', 'Grupo atualizado com sucesso.');
    }

    public function destroy(Grupo $grupo): RedirectResponse
    {
        $grupo->delete();

        return redirect()->route('grupos.index')->with('sucesso', 'Grupo removido com sucesso.');
    }
}
