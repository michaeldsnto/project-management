<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class PreventClientAccess
{
    /**
     * Handle an incoming request.
     * Prevent clients from accessing certain routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && method_exists(Auth::user(), 'isClient') && Auth::user()->isClient()) {
            abort(403, 'Clients are not allowed to access this resource.');
        }

        return $next($request);
    }
}
