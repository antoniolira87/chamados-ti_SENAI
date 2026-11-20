<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CPF, data de nascimento e CEP foram criados só para o trabalho do SENAI.
 * O celular continua.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropColumn(['cpf', 'data_nascimento', 'cep']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('cpf', 11)->nullable()->unique()->after('email');
            $table->date('data_nascimento')->nullable()->after('cpf');
            $table->char('cep', 8)->nullable()->after('celular');
        });
    }
};
