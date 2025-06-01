<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Gérer une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ajouter les en-têtes de sécurité uniquement si activés dans la configuration
        if (env('SECURITY_ENABLE_CSP', true)) {
            $response->headers->set('Content-Security-Policy', 
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
                "style-src 'self' 'unsafe-inline'; " .
                "img-src 'self' data:; " .
                "font-src 'self'; " .
                "connect-src 'self';"
            );
        }

        if (env('SECURITY_ENABLE_XSS_PROTECTION', true)) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        if (env('SECURITY_ENABLE_CONTENT_TYPE_OPTIONS', true)) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (env('SECURITY_ENABLE_FRAME_OPTIONS', true)) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if (env('SECURITY_ENABLE_STRICT_TRANSPORT', true)) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Ajouter l'en-tête Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Ajouter l'en-tête Permissions-Policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
