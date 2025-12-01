<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     * Log user activities for audit purposes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            $user = Auth::user();
            $method = $request->method();
            $url = $request->fullUrl();

            // Only log important actions (POST, PUT, PATCH, DELETE)
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                Log::info('User Activity', [
                    'user_id' => $user->id,
                    'user_name' => $user->name ?? 'Unknown',
                    'user_role' => $user->role ?? 'N/A',
                    'method' => $method,
                    'url' => $url,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now(),
                ]);
            }
        }

        return $response;
    }
}
