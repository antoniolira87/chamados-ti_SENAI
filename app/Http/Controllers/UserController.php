<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::query()
            ->withCount(['chamadosAbertos', 'chamadosAtribuidos'])
            ->when($request->filled('perfil'), fn ($q) => $q->where('perfil', $request->perfil))
            ->when($request->filled('busca'), function ($q) use ($request) {
                $q->where(fn ($q) => $q->where('name', 'like', "%{$request->busca}%")
                    ->orWhere('email', 'like', "%{$request->busca}%"));
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('usuarios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'perfil' => ['required', Rule::in(array_keys(User::PERFIS))],
            'setor' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        User::create($dados);

        return redirect()->route('usuarios.index')->with('sucesso', 'Usuário cadastrado com sucesso.');
    }

    public function show(User $usuario): View
    {
        $usuario->loadCount(['chamadosAbertos', 'chamadosAtribuidos']);
        $chamados = $usuario->isTecnico() || $usuario->isAdmin()
            ? $usuario->chamadosAtribuidos()->with('categoria')->latest()->limit(10)->get()
            : $usuario->chamadosAbertos()->with('categoria')->latest()->limit(10)->get();

        return view('usuarios.show', compact('usuario', 'chamados'));
    }

    public function edit(User $usuario): View
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'perfil' => ['required', Rule::in(array_keys(User::PERFIS))],
            'setor' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        // Senha em branco mantém a atual.
        if (empty($dados['password'])) {
            unset($dados['password']);
        }

        // Evita que o administrador remova o próprio acesso.
        if ($usuario->is($request->user()) && $dados['perfil'] !== 'admin') {
            return back()->withInput()->withErrors(['perfil' => 'Você não pode remover seu próprio perfil de administrador.']);
        }

        $usuario->update($dados);

        return redirect()->route('usuarios.index')->with('sucesso', 'Usuário atualizado com sucesso.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return redirect()->route('usuarios.index')->with('erro', 'Você não pode excluir a própria conta.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('sucesso', 'Usuário removido com sucesso.');
    }
}
