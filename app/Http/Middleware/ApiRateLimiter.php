<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * Créer une nouvelle instance du middleware.
     *
     * @param  \Illuminate\Cache\RateLimiter  $limiter
     * @return void
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Gérer une requête entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int|null  $maxAttempts
     * @param  int|null  $decayMinutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $maxAttempts = null, $decayMinutes = null): Response
    {
        // Récupérer les paramètres depuis les variables d'environnement si non spécifiés
        $maxAttempts = $maxAttempts ?: env('SECURITY_RATE_LIMIT_ATTEMPTS', 5);
        $decayMinutes = $decayMinutes ?: env('SECURITY_RATE_LIMIT_DECAY_MINUTES', 1);

        // Créer une clé unique pour l'IP et la route
        $key = $this->resolveRequestSignature($request);

        // Incrémenter le compteur de tentatives
        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            // Journaliser les tentatives excessives
            Log::warning('Trop de tentatives API détectées', [
                'ip' => $request->ip(),
                'route' => $request->path(),
                'user_agent' => $request->userAgent()
            ]);

            return $this->buildTooManyAttemptsResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Ajouter les en-têtes de rate limiting à la réponse
        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
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
        // Utiliser l'IP et le chemin pour créer une signature unique
        return sha1(
            $request->ip() . '|' . $request->path() . '|' . 
            ($request->input('serial_key') ?: $request->input('token') ?: '')
        );
    }

    /**
     * Créer une réponse pour trop de tentatives.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'status' => 'error',
            'message' => 'Trop de tentatives. Veuillez réessayer dans ' . ceil($retryAfter / 60) . ' minute(s).',
            'retry_after' => $retryAfter,
        ], 429)->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    /**
     * Ajouter les en-têtes de rate limiting à la réponse.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @param  int  $maxAttempts
     * @param  int  $remainingAttempts
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        return $response;
    }

    /**
     * Calculer le nombre de tentatives restantes.
     *
     * @param  string  $key
     * @param  int  $maxAttempts
     * @return int
     */
    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key) + 1;
    }
}
