<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenceModeService;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class InjectLicenceModeToViews
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
    public function handle(Request $request, Closure $next): Response
    {
        // Injecter les données du mode de licence dans toutes les vues
        $currentMode = $this->licenceModeService->getCurrentMode();
        $availableFeatures = $this->licenceModeService->getAvailableFeatures();
        $currentLimits = $this->licenceModeService->getLimits();
        
        View::share([
            'licenceMode' => [
                'current' => $currentMode,
                'is_saas' => $this->licenceModeService->isSaasMode(),
                'is_single_account' => $this->licenceModeService->isSingleAccountMode(),
                'features' => $availableFeatures,
                'limits' => $currentLimits,
                'display_name' => $currentMode === 'saas' ? 'SaaS Multi-tenant' : 'Mono-compte',
                'badge_class' => $currentMode === 'saas' ? 'badge-success' : 'badge-primary',
                'icon' => $currentMode === 'saas' ? 'fas fa-cloud' : 'fas fa-user'
            ]
        ]);
        
        // Ajouter des helpers pour les vues
        View::share([
            'hasFeature' => function($feature) {
                return $this->licenceModeService->hasFeature($feature);
            },
            'isLimitReached' => function($limitType) {
                return $this->licenceModeService->isLimitReached($limitType);
            },
            'canAccessRoute' => function($routeName) {
                return $this->licenceModeService->isRouteAccessible($routeName);
            }
        ]);
        
        return $next($request);
    }
}