<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Blocos de prompt da IANNE personalizados por município. Uma linha por tenant;
 * ausência da linha significa "usa tudo padrão". Ver App\Services\PromptSettings.
 */
class TenantAiPrompt extends Model
{
    protected $fillable = [
        'tenant_id',
        'blocks',
    ];

    protected $casts = [
        'blocks' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
