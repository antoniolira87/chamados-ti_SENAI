<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(): View
    {
        $categorias = Categoria::withCount('chamados')->orderBy('nome')->paginate(10);

        return view('categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        return view('categorias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:100', 'unique:categorias,nome'],
            'descricao' => ['nullable', 'string', 'max:2000'],
        ]);

        Categoria::create($dados);

        return redirect()->route('categorias.index')->with('sucesso', 'Categoria cadastrada com sucesso.');
    }

    public function show(Categoria $categoria): RedirectResponse
    {
        return redirect()->route('chamados.index', ['categoria_id' => $categoria->id]);
    }

    public function edit(Categoria $categoria): View
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:100', Rule::unique('categorias', 'nome')->ignore($categoria->id)],
            'descricao' => ['nullable', 'string', 'max:2000'],
        ]);

        $categoria->update($dados);

        return redirect()->route('categorias.index')->with('sucesso', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->chamados()->exists()) {
            return redirect()->route('categorias.index')
                ->with('erro', 'Não é possível excluir uma categoria que possui chamados.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('sucesso', 'Categoria removida com sucesso.');
    }
}
