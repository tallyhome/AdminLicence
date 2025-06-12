<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenceModeService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckLimits
{
    protected $licenceModeService;

    public function __construct(LicenceModeService $licenceModeService)
    {
        $this->licenceModeService = $licenceModeService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $limitType = null): Response
    {
        // Si aucun type de limite spécifié, on continue
        if (!$limitType) {
            return $next($request);
        }
        
        // Vérifier si la limite est atteinte
        if ($this->licenceModeService->isLimitReached($limitType)) {
            $limits = $this->licenceModeService->getLimits();
            $currentLimit = $limits[$limitType] ?? 'Non définie';
            
            Log::warning("Limite atteinte pour {$limitType}", [
                'limit_type' => $limitType,
                'current_limit' => $currentLimit,
                'route' => $request->route()->getName(),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Limite atteinte',
                    'message' => "La limite pour {$limitType} a été atteinte",
                    'limit_type' => $limitType,
                    'current_limit' => $currentLimit,
                    'upgrade_required' => !$this->licenceModeService->isSaasMode()
                ], 429); // 429 Too Many Requests
            }
            
            $errorMessage = "La limite pour {$limitType} a été atteinte";
            if (!$this->licenceModeService->isSaasMode()) {
                $errorMessage .= ". Passez en mode SaaS pour augmenter vos limites.";
            }
            
            return redirect()->back()
                ->with('error', $errorMessage)
                ->with('limit_reached', true)
                ->with('limit_type', $limitType);
        }
        
        return $next($request);
    }
    
    /**
     * Vérifier plusieurs types de limites
     */
    public function checkMultipleLimits(Request $request, Closure $next, ...$limitTypes): Response
    {
        foreach ($limitTypes as $limitType) {
            $response = $this->handle($request, function($req) { return null; }, $limitType);
            if ($response !== null) {
                return $response;
            }
        }
        
        return $next($request);
    }
}