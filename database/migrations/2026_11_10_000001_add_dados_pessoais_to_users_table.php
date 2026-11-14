<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Guardados sem máscara, só os números.
            // Ficam nullable por causa dos usuários que já existiam;
            // o formulário passa a exigir os quatro na inclusão e na alteração.
            $table->char('cpf', 11)->nullable()->unique()->after('email');
            $table->date('data_nascimento')->nullable()->after('cpf');
            $table->char('celular', 11)->nullable()->after('data_nascimento');
            $table->char('cep', 8)->nullable()->after('celular');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropColumn(['cpf', 'data_nascimento', 'celular', 'cep']);
        });
    }
};
