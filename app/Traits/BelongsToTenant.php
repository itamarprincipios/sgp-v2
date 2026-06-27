<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait.
     * Applies the TenantScope and listens to creating event to automatically set tenant_id.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            // Apenas atribui o tenant_id se o usuário estiver autenticado,
            // se não for um superadmin e se o campo tenant_id do model
            // não estiver pré-preenchido.
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->role !== 'superadmin' && $user->tenant_id) {
                    if (empty($model->tenant_id)) {
                        $model->tenant_id = $user->tenant_id;
                    }
                }
            }
        });
    }

    /**
     * Get the tenant that owns this model.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
