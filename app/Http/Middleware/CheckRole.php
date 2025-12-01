<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();

        // Redirect to login if not authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // Ensure $roles is an array (for safety)
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // Check if user has any of the allowed roles
        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Unauthorized access. You do not have permission to access this resource.');
            return response('Forbidden', 403); // appease Intelephense
        }

        return $next($request);
    }
}
