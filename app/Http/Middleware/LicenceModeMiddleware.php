<?php

namespace App\Http\Middleware;

use App\Services\LicenceModeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class LicenceModeMiddleware
{
    /**
     * @var LicenceModeService
     */
    protected $licenceModeService;
    
    /**
     * Constructeur
     *
     * @param LicenceModeService $licenceModeService
     */
    public function __construct(LicenceModeService $licenceModeService)
    {
        $this->licenceModeService = $licenceModeService;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $requiredMode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $requiredMode = null): BaseResponse
    {
        $currentMode = $this->licenceModeService->getCurrentMode();
        $routeName = $request->route()->getName();
        
        // Si un mode spécifique est requis, vérifier la compatibilité
        if ($requiredMode && $currentMode !== $requiredMode) {
            return $this->handleIncompatibleMode($request, $requiredMode, $currentMode);
        }
        
        // Vérifier si la route est accessible dans le mode actuel
        if (!$this->licenceModeService->isRouteAccessible($routeName)) {
            return $this->handleRestrictedRoute($request, $routeName, $currentMode);
        }
        
        // Ajouter des informations sur le mode dans la requête
        $request->merge([
            'licence_mode' => $currentMode,
            'available_features' => $this->licenceModeService->getAvailableFeatures(),
            'limits' => $this->licenceModeService->getLimits(),
        ]);
        
        return $next($request);
    }
    
    /**
     * Gère les cas où le mode requis ne correspond pas au mode actuel
     *
     * @param Request $request
     * @param string $requiredMode
     * @param string $currentMode
     * @return BaseResponse
     */
    protected function handleIncompatibleMode(Request $request, string $requiredMode, string $currentMode): BaseResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Mode de licence incompatible',
                'message' => "Cette fonctionnalité nécessite le mode {$requiredMode}, mais l'application est en mode {$currentMode}",
                'required_mode' => $requiredMode,
                'current_mode' => $currentMode,
            ], 403);
        }
        
        return redirect()->route('admin.dashboard')
            ->with('error', "Cette fonctionnalité nécessite le mode {$requiredMode}. Votre licence actuelle est en mode {$currentMode}.");
    }
    
    /**
     * Gère les routes restreintes selon le mode
     *
     * @param Request $request
     * @param string $routeName
     * @param string $currentMode
     * @return BaseResponse
     */
    protected function handleRestrictedRoute(Request $request, string $routeName, string $currentMode): BaseResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Fonctionnalité non disponible',
                'message' => "Cette fonctionnalité n'est pas disponible dans le mode {$currentMode}",
                'current_mode' => $currentMode,
                'route' => $routeName,
            ], 403);
        }
        
        $modeLabel = $currentMode === LicenceModeService::MODE_SAAS ? 'SaaS' : 'mono-compte';
        
        return redirect()->route('admin.dashboard')
            ->with('error', "Cette fonctionnalité n'est pas disponible dans le mode {$modeLabel}.");
    }
}