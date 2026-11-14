<?php

namespace App\Models;

use App\Rules\Cpf;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'cpf', 'data_nascimento', 'celular', 'cep', 'password', 'perfil', 'setor', 'grupo_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PERFIS = [
        'admin' => 'Administrador',
        'tecnico' => 'Técnico',
        'usuario' => 'Usuário',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'data_nascimento' => 'date',
            'password' => 'hashed',
        ];
    }

    /** Dados guardados só com números e mostrados com máscara. */
    public function getCpfFormatadoAttribute(): string
    {
        return Cpf::formatar($this->cpf);
    }

    public function getCelularFormatadoAttribute(): string
    {
        return $this->celular && strlen($this->celular) === 11
            ? '('.substr($this->celular, 0, 2).') '.substr($this->celular, 2, 5).'-'.substr($this->celular, 7)
            : (string) $this->celular;
    }

    public function getCepFormatadoAttribute(): string
    {
        return $this->cep && strlen($this->cep) === 8
            ? substr($this->cep, 0, 5).'-'.substr($this->cep, 5)
            : (string) $this->cep;
    }

    public function getIdadeAttribute(): ?int
    {
        return $this->data_nascimento?->age;
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    public function chamadosAbertos(): HasMany
    {
        return $this->hasMany(Chamado::class, 'solicitante_id');
    }

    public function chamadosAtribuidos(): HasMany
    {
        return $this->hasMany(Chamado::class, 'tecnico_id');
    }

    public function isAdmin(): bool
    {
        return $this->perfil === 'admin';
    }

    public function isTecnico(): bool
    {
        return $this->perfil === 'tecnico';
    }

    /** Técnicos e administradores podem atender chamados. */
    public function podeAtender(): bool
    {
        return $this->isAdmin() || $this->isTecnico();
    }

    public function getPerfilLabelAttribute(): string
    {
        return self::PERFIS[$this->perfil] ?? $this->perfil;
    }
}
