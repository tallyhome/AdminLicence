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
            
            // Forcer la vérification sans utiliser le cache
            // Nous désactivons temporairement le cache pour garantir une vérification fraîche
            \Illuminate\Support\Facades\Cache::forget('license_verification_' . md5($licenseKey));
            
            // Vérifier directement avec l'API
            $domain = $request->getHost();
            $ipAddress = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
            
            // Vérifier directement la validité de la clé avec l'API
            $result = $this->licenceService->validateSerialKey($licenseKey, $domain, $ipAddress);
            $isValid = $result['valid'] === true;
            
            // Journaliser le résultat pour le débogage
            Log::info('Vérification de licence dans le middleware', [
                'license_key' => $licenseKey,
                'valid' => $isValid,
                'message' => $result['message'] ?? 'Aucun message',
                'path' => $request->path(),
                'data' => $result['data'] ?? []
            ]);
            
            // Stocker le résultat en cache
            \Illuminate\Support\Facades\Cache::put('license_verification_' . md5($licenseKey), $isValid, 60 * 24);
            
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
