<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LicenseVerificationRateLimiter
{
    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 1): Response
    {
        // Identifier unique pour le rate limiting
        $key = $this->resolveRequestSignature($request);

        // Si la limite est dépassée
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            Log::warning('Trop de tentatives de vérification de licence', [
                'ip' => $request->ip(),
                'key' => $key,
                'attempts' => $this->limiter->attempts($key),
                'max_attempts' => $maxAttempts
            ]);

            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        // Incrémenter le compteur de tentatives
        $this->limiter->hit($key, $decayMinutes * 60);

        // Ajouter les en-têtes de rate limiting à la réponse
        $response = $next($request);
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->limiter->attempts($key),
            $this->limiter->availableIn($key)
        );
    }

    /**
     * Résoudre la signature de la requête pour le rate limiting.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // Utiliser l'IP et éventuellement la clé de licence pour l'identifiant
        $serialKey = $request->input('serial_key') ?? $request->input('license_key') ?? '';
        
        // Créer un identifiant unique pour cette vérification
        return sha1($request->ip() . '|license_verification|' . $serialKey);
    }

    /**
     * Créer une réponse pour les tentatives trop nombreuses.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        $headers = [
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => time() + $retryAfter,
        ];

        return response()->json([
            'success' => false,
            'message' => 'Trop de tentatives de vérification de licence. Veuillez réessayer dans ' . ceil($retryAfter / 60) . ' minute(s).',
            'retry_after' => $retryAfter,
            'status' => 429
        ], 429, $headers);
    }

    /**
     * Ajouter les en-têtes de rate limiting à la réponse.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @param  int|null  $retryAfter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts, ?int $retryAfter = null): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - $remainingAttempts),
        ]);

        if (! is_null($retryAfter)) {
            $response->headers->add([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Reset' => time() + $retryAfter,
            ]);
        }

        return $response;
    }
}
