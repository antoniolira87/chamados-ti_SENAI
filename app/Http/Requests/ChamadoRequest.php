<?php

namespace App\Http\Requests;

use App\Models\Chamado;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Regras do formulário de incidentes (registro e alteração).
 * O JavaScript do formulário só ajuda o usuário; quem garante os dados é esta classe.
 */
class ChamadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // a autorização por registro é feita no controller
    }

    /** Remove espaços desnecessários antes de validar. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Título: tira espaços do início, do fim e repetidos no meio.
            'titulo' => is_string($this->titulo) ? preg_replace('/\s+/u', ' ', trim($this->titulo)) : $this->titulo,
            // Descrição: tira só do início e do fim, para manter as quebras de linha.
            'descricao' => is_string($this->descricao) ? trim($this->descricao) : $this->descricao,
        ]);
    }

    public function rules(): array
    {
        $novo = $this->route('chamado') === null;

        return [
            'titulo' => ['required', 'string', 'min:3', 'max:100'],
            'descricao' => ['required', 'string', 'min:10', 'max:5000'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            // A prioridade é calculada pelo model a partir destes dois campos.
            'impacto' => ['required', Rule::in(array_keys(Chamado::IMPACTOS))],
            'urgencia' => ['required', Rule::in(array_keys(Chamado::URGENCIAS))],
            // Período: o problema não começa no futuro e o prazo não termina antes do início.
            'inicio_problema' => ['bail', 'required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'prazo' => ['bail', 'required', 'date_format:Y-m-d', 'after_or_equal:inicio_problema',
                // Num incidente novo, o prazo também não pode já ter passado.
                function (string $attribute, mixed $value, Closure $fail) use ($novo) {
                    if ($novo && $value < today()->toDateString()) {
                        $fail('O prazo para resolver não pode ser uma data que já passou.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.min' => 'A descrição resumida deve ter pelo menos 3 caracteres.',
            'titulo.max' => 'A descrição resumida pode ter no máximo 100 caracteres.',
            'descricao.min' => 'Explique melhor o problema: escreva pelo menos 10 caracteres.',
            'inicio_problema.date_format' => 'Informe uma data de início que exista no calendário (dia/mês/ano).',
            'inicio_problema.before_or_equal' => 'O problema não pode ter começado numa data futura.',
            'prazo.date_format' => 'Informe um prazo que exista no calendário (dia/mês/ano).',
            'prazo.after_or_equal' => 'Período inconsistente: o prazo não pode ser anterior à data em que o problema começou.',
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'descrição resumida',
            'descricao' => 'descrição',
            'categoria_id' => 'categoria',
            'impacto' => 'impacto',
            'urgencia' => 'urgência',
            'inicio_problema' => 'problema começou em',
            'prazo' => 'prazo para resolver',
        ];
    }
}
