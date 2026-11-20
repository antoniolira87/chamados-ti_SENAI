<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Regras do cadastro de usuários, usadas em três lugares:
 * inclusão pelo administrador, alteração pelo administrador e o "Cadastre-se".
 * O JavaScript do formulário só ajuda o usuário; quem garante os dados é esta classe.
 */
class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // o acesso já é controlado pelas rotas
    }

    /** Normaliza os dados antes de validar. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Remove espaços no início, no fim e repetidos no meio.
            'name' => is_string($this->name) ? Str::squish($this->name) : $this->name,
            'email' => is_string($this->email) ? Str::lower(trim($this->email)) : $this->email,
            // Aceita (11) 98765-4321; se tiver letra ou outro símbolo, a validação recusa.
            'celular' => $this->somenteNumeros($this->celular, '/^[\d()\-\s]+$/'),
        ]);
    }

    public function rules(): array
    {
        $usuario = $this->route('usuario');
        $autoCadastro = $this->is('cadastro'); // "Cadastre-se": sem perfil nem grupo

        $regras = [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['bail', 'required', 'string', 'max:100', 'email:rfc', 'regex:/^[^@\s]+@[^@\s]+\.[a-z]{2,}$/i',
                Rule::unique('users', 'email')->ignore($usuario?->id)],
            'celular' => ['bail', 'required', 'digits:11', 'regex:/^[1-9]{2}9\d{8}$/'],
            'setor' => ['nullable', 'string', 'max:100'],
            'password' => [$usuario ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
        ];

        if (! $autoCadastro) {
            $regras['perfil'] = ['required', Rule::in(array_keys(User::PERFIS))];
            $regras['grupo_id'] = ['nullable', 'exists:grupos,id'];
        }

        return $regras;
    }

    public function messages(): array
    {
        return [
            'name.min' => 'O nome deve ter pelo menos 3 letras.',
            'name.max' => 'O nome pode ter no máximo 100 caracteres.',
            'email.email' => 'Informe um e-mail válido, por exemplo: nome@empresa.com.',
            'email.regex' => 'Informe um e-mail válido, por exemplo: nome@empresa.com.',
            'celular.digits' => 'O celular deve ter DDD + 9 números, por exemplo: (11) 98765-4321.',
            'celular.regex' => 'O número do celular deve começar com 9 logo depois do DDD.',
        ];
    }

    private function somenteNumeros(mixed $valor, string $formatoAceito): mixed
    {
        if (! is_string($valor)) {
            return $valor;
        }

        $valor = trim($valor);

        return preg_match($formatoAceito, $valor) ? preg_replace('/\D/', '', $valor) : $valor;
    }
}
