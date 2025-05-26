<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminRedirectAfterLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si l'utilisateur est authentifié en tant qu'admin et essaie d'accéder à la page de connexion
        if (Auth::guard('admin')->check() && $request->is('admin/login')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
