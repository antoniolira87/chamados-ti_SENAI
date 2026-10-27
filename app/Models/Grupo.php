<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Grupo de atribuição: equipe que recebe e trata os incidentes. */
class Grupo extends Model
{
    protected $fillable = ['nome', 'descricao'];

    public function membros(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function chamados(): HasMany
    {
        return $this->hasMany(Chamado::class);
    }
}
