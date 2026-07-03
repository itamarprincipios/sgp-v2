<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQL equivalente já executado em produção em 03/07/2026 (spec §2.3).
     * Guardas hasColumn evitam erro caso rode num banco já alterado.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'plan')) {
                $table->enum('plan', ['coordenador', 'escola', 'semed'])
                    ->default('semed')->after('slug');
            }
            if (!Schema::hasColumn('tenants', 'billing_type')) {
                $table->enum('billing_type', ['mensal', 'vitalicio'])
                    ->nullable()->after('plan');
            }
            if (!Schema::hasColumn('tenants', 'ai_monthly_limit')) {
                $table->unsignedInteger('ai_monthly_limit')
                    ->nullable()->after('ai_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['plan', 'billing_type', 'ai_monthly_limit']);
        });
    }
};
