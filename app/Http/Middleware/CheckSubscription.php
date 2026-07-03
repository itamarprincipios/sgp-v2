<?php
// app/Http/Middleware/CheckSubscription.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforcement da assinatura (spec §3): tenant vencido/desativado entra em
 * modo somente leitura — GET passa (com banner), escrita é barrada.
 * Superadmin (sem tenant) nunca é afetado. Logout fica em routes/auth.php,
 * fora deste grupo, então segue acessível.
 */
class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $user?->tenant;

        if (!$user || $user->role === 'superadmin' || !$tenant) {
            return $next($request);
        }

        if (!$tenant->isBlocked()) {
            return $next($request);
        }

        View::share('subscriptionReadOnly', true);

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        $message = 'Assinatura vencida — modo somente leitura. Fale conosco para renovar.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return back()->with('error', $message);
    }
}
