<?php

namespace App\Http\Requests;

use App\Models\Chamado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChamadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // a autorização por registro é feita no controller
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'max:5000'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            // A prioridade é calculada pelo model a partir destes dois campos.
            'impacto' => ['required', Rule::in(array_keys(Chamado::IMPACTOS))],
            'urgencia' => ['required', Rule::in(array_keys(Chamado::URGENCIAS))],
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
        ];
    }
}
