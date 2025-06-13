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
            // Commenté : Ne plus autoriser automatiquement l'accès en local
            // La licence doit être valide même en environnement de développement
            // if (env('APP_ENV') === 'local' && env('APP_DEBUG') === true) {
            //     return $next($request);
            // }
            
            // Récupérer la clé de licence directement depuis le fichier .env
            $licenseKey = $this->getLicenseKeyFromEnv();
            
            // Vérifier si une clé de licence est configurée
            if (empty($licenseKey)) {
                Log::warning('Tentative d\'accès sans clé de licence configurée: ' . $request->path());
                return redirect()->route('admin.settings.license')
                    ->with('error', 'Aucune clé de licence n\'est configurée. Veuillez configurer une licence valide pour continuer à utiliser le système.');
            }
            
            // Utiliser la méthode optimisée du service pour vérifier la licence
            $isValid = $this->licenceService->verifyInstallationLicense();
            
            // Si la licence n'est pas valide, rediriger vers la page de licence
            if (!$isValid) {
                Log::warning('Tentative d\'accès avec une licence invalide: ' . $request->path());
                return redirect()->route('admin.settings.license')
                    ->with('error', 'Votre licence d\'installation n\'est pas valide. Veuillez vérifier votre clé de licence.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de licence dans le middleware', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'path' => $request->path()
            ]);
            
            // Commenté : Ne plus autoriser l'accès en cas d'erreur même en local
            // La licence doit être valide dans tous les environnements
            // if (env('APP_ENV') === 'local') {
            //     Log::warning('Erreur de vérification en environnement local, accès autorisé');
            //     return $next($request);
            // }
            
            return redirect()->route('admin.settings.license')
                ->with('error', 'Erreur lors de la vérification de licence: ' . $e->getMessage());
        }

        return $next($request);
    }
    
    /**
     * Récupérer la clé de licence directement depuis le fichier .env
     * pour éviter les problèmes de cache
     *
     * @return string|null
     */
    private function getLicenseKeyFromEnv()
    {
        $path = base_path('.env');
        
        if (!\Illuminate\Support\Facades\File::exists($path)) {
            return null;
        }
        
        $content = \Illuminate\Support\Facades\File::get($path);
        
        // Chercher la ligne INSTALLATION_LICENSE_KEY
        if (preg_match('/^INSTALLATION_LICENSE_KEY=(.*)$/m', $content, $matches)) {
            $value = trim($matches[1]);
            // Supprimer les guillemets si présents
            $value = trim($value, '"\'\'');
            // Supprimer l'échappement des caractères
            $value = stripslashes($value);
            return !empty($value) ? $value : null;
        }
        
        return null;
    }
}
