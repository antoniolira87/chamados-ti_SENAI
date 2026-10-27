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
            'prioridade' => ['required', Rule::in(array_keys(Chamado::PRIORIDADES))],
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'descricao' => 'descrição',
            'categoria_id' => 'categoria',
            'prioridade' => 'prioridade',
        ];
    }
}
