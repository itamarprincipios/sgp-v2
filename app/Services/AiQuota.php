<?php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Cota mensal de IA por tenant (spec §5). ai_monthly_limit null = ilimitado
 * (período de testes). O contador registra SEMPRE, mesmo sem limite, para
 * gerar dado real de consumo antes da precificação.
 */
class AiQuota
{
    public static function assertAvailable(?Tenant $tenant): void
    {
        if (!$tenant || $tenant->ai_monthly_limit === null) {
            return;
        }

        $used = DB::table('ai_usage')
            ->where('tenant_id', $tenant->id)
            ->where('period', now()->format('Y-m'))
            ->value('count') ?? 0;

        if ($used >= $tenant->ai_monthly_limit) {
            throw new AiQuotaExceededException();
        }
    }

    public static function record(?int $tenantId): void
    {
        if (!$tenantId) {
            return; // chamadas do superadmin não contam contra ninguém
        }

        DB::statement(
            'INSERT INTO ai_usage (tenant_id, period, count) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE count = count + 1',
            [$tenantId, now()->format('Y-m')]
        );
    }
}
