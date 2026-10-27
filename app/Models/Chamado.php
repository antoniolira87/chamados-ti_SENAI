<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chamado extends Model
{
    public const STATUS = [
        'aberto' => 'Aberto',
        'em_andamento' => 'Em andamento',
        'aguardando' => 'Aguardando usuário',
        'resolvido' => 'Resolvido',
        'fechado' => 'Fechado',
    ];

    public const STATUS_CORES = [
        'aberto' => 'primary',
        'em_andamento' => 'warning',
        'aguardando' => 'info',
        'resolvido' => 'success',
        'fechado' => 'secondary',
    ];

    public const PRIORIDADES = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public const PRIORIDADE_CORES = [
        'baixa' => 'secondary',
        'media' => 'info',
        'alta' => 'warning',
        'urgente' => 'danger',
    ];

    protected $fillable = [
        'titulo', 'descricao', 'prioridade', 'status',
        'categoria_id', 'solicitante_id', 'tecnico_id', 'resolvido_em',
    ];

    protected function casts(): array
    {
        return ['resolvido_em' => 'datetime'];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(ChamadoHistorico::class)->latest();
    }

    /** Usuários comuns só enxergam os próprios chamados. */
    public function scopeVisivelPara(Builder $query, User $user): Builder
    {
        return $user->podeAtender() ? $query : $query->where('solicitante_id', $user->id);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getStatusCorAttribute(): string
    {
        return self::STATUS_CORES[$this->status] ?? 'secondary';
    }

    public function getPrioridadeLabelAttribute(): string
    {
        return self::PRIORIDADES[$this->prioridade] ?? $this->prioridade;
    }

    public function getPrioridadeCorAttribute(): string
    {
        return self::PRIORIDADE_CORES[$this->prioridade] ?? 'secondary';
    }
}
