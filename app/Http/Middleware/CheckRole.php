<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // If user is admin, allow everything? Maybe. 
        // For now, let's strict check. Or allow admin always.
        if ($user->role === 'admin') {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
