<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'perfil', 'setor'])]
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
            'password' => 'hashed',
        ];
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
