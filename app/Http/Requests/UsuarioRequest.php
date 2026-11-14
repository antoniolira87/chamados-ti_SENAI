<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\Cpf;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Regras do cadastro de usuários, usadas em três lugares:
 * inclusão pelo administrador, alteração pelo administrador e o "Cadastre-se".
 * O JavaScript do formulário só ajuda o usuário; quem garante os dados é esta classe.
 */
class UsuarioRequest extends FormRequest
{
    public const IDADE_MINIMA = 18;

    public const IDADE_MAXIMA = 100;

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
            // Máscaras aceitas: 123.456.789-09, 12345-678 e (11) 98765-4321.
            // Se tiver letra ou outro símbolo, o valor fica como veio e a validação recusa.
            'cpf' => $this->somenteNumeros($this->cpf, '/^[\d.\-\s]+$/'),
            'cep' => $this->somenteNumeros($this->cep, '/^\d{5}-?\d{3}$/'),
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
            'data_nascimento' => ['bail', 'required', 'date_format:Y-m-d', 'before_or_equal:today', $this->regraIdade()],
            'cpf' => ['bail', 'required', 'digits:11', new Cpf, Rule::unique('users', 'cpf')->ignore($usuario?->id)],
            'cep' => ['bail', 'required', 'digits:8'],
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
            'data_nascimento.date_format' => 'Informe uma data de nascimento que exista no calendário (dia/mês/ano).',
            'data_nascimento.before_or_equal' => 'A data de nascimento não pode estar no futuro.',
            'cpf.digits' => 'O CPF deve ter exatamente 11 números (pode digitar com ou sem pontos e traço).',
            'cpf.unique' => 'Já existe um usuário cadastrado com este CPF.',
            'cep.digits' => 'O CEP deve estar no formato 12345-678 ou 12345678, somente com números.',
            'celular.digits' => 'O celular deve ter DDD + 9 números, por exemplo: (11) 98765-4321.',
            'celular.regex' => 'O número do celular deve começar com 9 logo depois do DDD.',
        ];
    }

    /** Idade calculada pela data de nascimento: entre 18 e 100 anos completos. */
    private function regraIdade(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            $idade = Carbon::createFromFormat('!Y-m-d', $value)->age;

            if ($idade < self::IDADE_MINIMA) {
                $fail('É preciso ter pelo menos '.self::IDADE_MINIMA.' anos completos.');
            } elseif ($idade > self::IDADE_MAXIMA) {
                $fail('A idade máxima permitida é de '.self::IDADE_MAXIMA.' anos. Confira o ano de nascimento.');
            }
        };
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
