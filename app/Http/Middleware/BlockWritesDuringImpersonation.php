<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockWritesDuringImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('impersonator_id')) {
            return $next($request);
        }

        if (! $request->user()) {
            $request->session()->forget(['impersonator_id', 'impersonator_name', 'impersonated_user_id']);

            return $next($request);
        }

        if ($request->routeIs('admin.impersonation.stop')) {
            return $next($request);
        }

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        abort(403, __('Read-only admin preview mode prevents changes to this user account.'));
    }
}
