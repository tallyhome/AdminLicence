<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenceModeService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckLicenceMode
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
    public function handle(Request $request, Closure $next, string $requiredMode = null): Response
    {
        $currentMode = $this->licenceModeService->getCurrentMode();
        
        // Si un mode spécifique est requis
        if ($requiredMode) {
            if ($currentMode !== $requiredMode) {
                Log::warning("Accès refusé - Mode requis: {$requiredMode}, Mode actuel: {$currentMode}", [
                    'route' => $request->route()->getName(),
                    'user_id' => auth()->id(),
                    'ip' => $request->ip()
                ]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Accès non autorisé',
                        'message' => "Cette fonctionnalité nécessite le mode {$requiredMode}",
                        'current_mode' => $currentMode,
                        'required_mode' => $requiredMode
                    ], 403);
                }
                
                return redirect()->route('admin.dashboard')
                    ->with('error', "Cette fonctionnalité nécessite le mode {$requiredMode}. Mode actuel: {$currentMode}");
            }
        }
        
        // Vérifier si la route est accessible selon le mode
        $routeName = $request->route()->getName();
        if (!$this->licenceModeService->isRouteAccessible($routeName)) {
            Log::warning("Route non accessible en mode {$currentMode}", [
                'route' => $routeName,
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Route non accessible',
                    'message' => "Cette route n'est pas accessible en mode {$currentMode}",
                    'current_mode' => $currentMode
                ], 403);
            }
            
            return redirect()->route('admin.dashboard')
                ->with('error', "Cette fonctionnalité n'est pas disponible en mode {$currentMode}");
        }
        
        // Injecter le mode dans la vue
        view()->share('currentLicenceMode', $currentMode);
        view()->share('isSaasMode', $this->licenceModeService->isSaasMode());
        view()->share('isSingleAccountMode', $this->licenceModeService->isSingleAccountMode());
        view()->share('availableFeatures', $this->licenceModeService->getAvailableFeatures());
        view()->share('currentLimits', $this->licenceModeService->getLimits());
        
        return $next($request);
    }
}