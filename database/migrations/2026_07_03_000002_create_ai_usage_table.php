<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tabela já criada em produção em 03/07/2026 (spec §2.3). */
    public function up(): void
    {
        if (Schema::hasTable('ai_usage')) {
            return;
        }
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->char('period', 7); // "2026-07"
            $table->unsignedInteger('count')->default(0);
            $table->unique(['tenant_id', 'period'], 'ai_usage_tenant_period');
        });
    }

    public function down(): void
    {
        // Sem rollback: tabela criada manualmente em produção (spec §2.3),
        // contém histórico de uso de IA. Remover só via SQL manual.
    }
};
