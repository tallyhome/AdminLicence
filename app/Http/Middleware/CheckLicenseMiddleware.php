<?php

namespace App\Http\Middleware;

use App\Services\LicenceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class CheckLicenseMiddleware
{
    protected $licenceService;

    public function __construct(LicenceService $licenceService)
    {
        $this->licenceService = $licenceService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Ne pas vérifier pour la page de licence elle-même et les routes d'authentification
        if ($request->is('admin/settings/license') || 
            $request->is('admin/settings/license/*') || 
            $request->is('admin/login') || 
            $request->is('admin/logout')) {
            return $next($request);
        }

        try {
            // Récupérer la clé de licence
            $licenseKey = env('INSTALLATION_LICENSE_KEY');
            
            // Vérifier si une clé de licence est configurée
            if (empty($licenseKey)) {
                Log::warning('Tentative d\'accès sans clé de licence configurée: ' . $request->path());
                return redirect()->route('admin.settings.license')
                    ->with('error', 'Aucune clé de licence n\'est configurée. Veuillez configurer une licence valide pour continuer à utiliser le système.');
            }
            
            // Vérifier si nous avons un résultat en cache
            $cacheKey = 'license_verification_' . md5($licenseKey);
            $cachedResult = \Illuminate\Support\Facades\Cache::get($cacheKey);
            
            // Vérifier si nous devons faire une nouvelle vérification API
            // 1. Si aucun résultat en cache
            // 2. Si la dernière vérification date de plus de 6 heures
            $lastCheckKey = 'last_license_check_' . md5($licenseKey);
            $lastCheck = \Illuminate\Support\Facades\Cache::get($lastCheckKey, 0);
            $currentTime = time();
            $eightHoursInSeconds = 8 * 60 * 60;
            
            if ($cachedResult === null || ($currentTime - $lastCheck) > $eightHoursInSeconds) {
                // Vérifier directement avec l'API
                $domain = $request->getHost();
                $ipAddress = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
                
                // Vérifier directement la validité de la clé avec l'API
                $result = $this->licenceService->validateSerialKey($licenseKey, $domain, $ipAddress);
                $isValid = $result['valid'] === true;
                
                // Mettre à jour le timestamp de la dernière vérification
                \Illuminate\Support\Facades\Cache::put($lastCheckKey, $currentTime, 60 * 24 * 7); // 7 jours
            } else {
                // Utiliser le résultat en cache
                $isValid = $cachedResult;
                $result = ['valid' => $isValid, 'message' => 'Résultat en cache', 'data' => []]; 
            }
            
            // Journaliser uniquement les échecs ou les changements de statut
            if (!$isValid) {
                // Journaliser les échecs de validation de licence
                Log::warning('Licence invalide dans le middleware', [
                    'message' => $result['message'] ?? 'Aucun détail disponible',
                    'path' => $request->path()
                ]);
            } elseif (!$cachedResult && $isValid) {
                // Journaliser uniquement la première validation réussie ou après un changement de statut
                Log::info('Licence validée avec succès', [
                    'path' => $request->path()
                ]);
            }
            
            // Stocker le résultat en cache (24 heures)
            \Illuminate\Support\Facades\Cache::put($cacheKey, $isValid, 60 * 24);
            
            // Si la licence n'est pas valide, rediriger vers la page de licence
            if (!$isValid) {
                Log::warning('Tentative d\'accès avec une licence invalide: ' . $request->path());
                return redirect()->route('admin.settings.license')
                    ->with('error', 'Votre licence d\'installation n\'est pas valide. Message: ' . ($result['message'] ?? 'Aucun détail disponible') . '.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de licence dans le middleware', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'path' => $request->path()
            ]);
            
            return redirect()->route('admin.settings.license')
                ->with('error', 'Erreur lors de la vérification de licence: ' . $e->getMessage());
        }

        return $next($request);
    }
}
