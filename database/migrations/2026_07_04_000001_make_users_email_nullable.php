<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Professor vira cadastro sem login (pivô — plataforma de correção): a IA cria
 * o professor a partir do documento, sem e-mail. O índice unique permanece;
 * o MySQL aceita múltiplos NULL num índice unique.
 *
 * SQL equivalente já executado em produção via phpMyAdmin (autodeploy não roda
 * migrations). Rodar novamente é inócuo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
    }

    public function down(): void
    {
        // Sem rollback automático: reverter exigiria garantir que não há
        // professores sem e-mail. Reverter só via SQL manual e consciente.
    }
};
