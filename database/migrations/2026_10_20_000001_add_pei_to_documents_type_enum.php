<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PEI (Plano Educacional Individualizado) vira tipo próprio de documento, com
 * rubrica de auditoria separada da de planejamento e da de relatório.
 *
 * O autodeploy da Hostinger NÃO roda migrations — o SQL equivalente está em
 * .agents/PIVO-STATUS.md e precisa ser executado no phpMyAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('documents') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE documents MODIFY type ENUM('planejamento','relatorio','pei','outro') NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('documents') || DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE documents SET type = 'outro' WHERE type = 'pei'");
        DB::statement("ALTER TABLE documents MODIFY type ENUM('planejamento','relatorio','outro') NOT NULL");
    }
};
