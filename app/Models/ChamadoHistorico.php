<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Item da linha do tempo (Atividades) de um incidente. */
class ChamadoHistorico extends Model
{
    public const TIPOS = [
        'comentario' => 'Comentário',      // visível ao solicitante
        'nota' => 'Nota de trabalho',      // interna, só técnicos/admin
    ];

    protected $fillable = ['chamado_id', 'user_id', 'tipo', 'status_anterior', 'status_novo', 'comentario'];

    public function chamado(): BelongsTo
    {
        return $this->belongsTo(Chamado::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Notas de trabalho ficam ocultas para o solicitante. */
    public function scopeVisivelPara(Builder $query, User $user): Builder
    {
        return $user->podeAtender() ? $query : $query->where('tipo', '!=', 'nota');
    }

    public function isNota(): bool
    {
        return $this->tipo === 'nota';
    }
}
