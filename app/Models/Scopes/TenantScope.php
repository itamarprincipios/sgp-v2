<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    private static $applying = false;

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Evita recursão infinita ao autenticar/recuperar o próprio usuário
        if (self::$applying) {
            return;
        }

        self::$applying = true;

        try {
            // Ignora se não houver um usuário autenticado no sistema.
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            // O Super Admin possui acesso irrestrito a todas as prefeituras
            if ($user->role === 'superadmin') {
                return;
            }

            // Filtra os resultados pelo tenant_id associado ao usuário
            if ($user->tenant_id) {
                $builder->where($model->getTable() . '.tenant_id', $user->tenant_id);
            }
        } finally {
            self::$applying = false;
        }
    }
}
