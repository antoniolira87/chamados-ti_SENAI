<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Incidente de TI (modelo inspirado nos processos ITSM/ITIL). */
class Chamado extends Model
{
    public const STATUS = [
        'aberto' => 'Novo',
        'em_andamento' => 'Em andamento',
        'aguardando' => 'Em espera',
        'resolvido' => 'Resolvido',
        'fechado' => 'Fechado',
        'cancelado' => 'Cancelado',
    ];

    public const STATUS_CORES = [
        'aberto' => 'primary',
        'em_andamento' => 'warning',
        'aguardando' => 'info',
        'resolvido' => 'success',
        'fechado' => 'secondary',
        'cancelado' => 'dark',
    ];

    /** Status em que o incidente deixa de estar pendente. */
    public const FINALIZADOS = ['resolvido', 'fechado', 'cancelado'];

    public const IMPACTOS = [1 => '1 - Alto', 2 => '2 - Médio', 3 => '3 - Baixo'];

    public const URGENCIAS = [1 => '1 - Alta', 2 => '2 - Média', 3 => '3 - Baixa'];

    public const PRIORIDADES = [
        1 => '1 - Crítica',
        2 => '2 - Alta',
        3 => '3 - Moderada',
        4 => '4 - Baixa',
        5 => '5 - Planejamento',
    ];

    public const PRIORIDADE_CORES = [
        1 => 'danger',
        2 => 'warning',
        3 => 'info',
        4 => 'secondary',
        5 => 'light',
    ];

    protected $fillable = [
        'titulo', 'descricao', 'impacto', 'urgencia', 'status',
        'categoria_id', 'grupo_id', 'solicitante_id', 'tecnico_id', 'resolvido_em',
    ];

    protected function casts(): array
    {
        return [
            'resolvido_em' => 'datetime',
            'impacto' => 'integer',
            'urgencia' => 'integer',
            'prioridade' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // A prioridade nunca é escolhida à mão: vem da matriz Impacto × Urgência.
        static::saving(function (Chamado $chamado) {
            $chamado->prioridade = self::calcularPrioridade($chamado->impacto ?? 3, $chamado->urgencia ?? 3);
        });

        static::created(function (Chamado $chamado) {
            $chamado->numero = self::gerarNumero($chamado->id);
            $chamado->saveQuietly();
        });
    }

    /**
     * Matriz de prioridade:
     *                 Urgência Alta  Média  Baixa
     *   Impacto Alto        1        2      3
     *   Impacto Médio       2        3      4
     *   Impacto Baixo       3        4      5
     */
    public static function calcularPrioridade(int $impacto, int $urgencia): int
    {
        return $impacto + $urgencia - 1;
    }

    public static function gerarNumero(int $id): string
    {
        return 'INC'.str_pad((string) (10000 + $id), 7, '0', STR_PAD_LEFT);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
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
        return $this->hasMany(ChamadoHistorico::class)->latest()->latest('id');
    }

    /** Usuários comuns só enxergam os próprios chamados. */
    public function scopeVisivelPara(Builder $query, User $user): Builder
    {
        return $user->podeAtender() ? $query : $query->where('solicitante_id', $user->id);
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::FINALIZADOS);
    }

    public function finalizado(): bool
    {
        return in_array($this->status, self::FINALIZADOS);
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
        return self::PRIORIDADES[$this->prioridade] ?? (string) $this->prioridade;
    }

    public function getPrioridadeCorAttribute(): string
    {
        return self::PRIORIDADE_CORES[$this->prioridade] ?? 'secondary';
    }

    public function getImpactoLabelAttribute(): string
    {
        return self::IMPACTOS[$this->impacto] ?? (string) $this->impacto;
    }

    public function getUrgenciaLabelAttribute(): string
    {
        return self::URGENCIAS[$this->urgencia] ?? (string) $this->urgencia;
    }
}
