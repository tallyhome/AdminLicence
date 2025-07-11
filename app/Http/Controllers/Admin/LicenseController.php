<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SerialKey;
use App\Models\Setting;
use App\Services\LicenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class LicenseController extends Controller
{
    /**
     * Service de licence
     *
     * @var LicenceService
     */
    protected $licenceService;

    /**
     * Constructeur du contrôleur
     *
     * @param LicenceService $licenceService
     */
    public function __construct(LicenceService $licenceService)
    {
        $this->licenceService = $licenceService;
    }

    /**
     * Afficher la page d'informations de licence
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupérer la clé de licence actuelle directement depuis le fichier .env
        // pour éviter les problèmes de cache
        $licenseKey = $this->getLicenseKeyFromEnv();
        
        // Récupérer la fréquence de vérification
        $checkFrequency = Setting::get('license_check_frequency', 5);
        $lastCheck = Setting::get('last_license_check');
        $licenseValid = Setting::get('license_valid', false);
        
        // Vérifier la validité de la licence
        $isValid = $licenseValid;
        $expiresAt = null;
        $licenseDetails = null;
        
        if ($licenseKey) {
            // Récupérer les détails de la licence
            $key = SerialKey::where('serial_key', $licenseKey)->first();
            if ($key) {
                $licenseDetails = $key;
                $expiresAt = $key->expires_at;
            }
        }
        
        // Vérifier si le fichier .env existe
        $envExists = File::exists(base_path('.env'));
        
        return view('admin.settings.license', compact(
            'licenseKey',
            'isValid',
            'expiresAt',
            'checkFrequency',
            'lastCheck',
            'licenseDetails',
            'envExists'
        ));
    }
    
    /**
     * Mettre à jour les paramètres de vérification de licence
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'license_key' => 'nullable|string|max:255'
        ]);
        
        // Traiter la clé de licence
        $licenseKey = $request->input('license_key');
        if (!empty($licenseKey)) {
            try {
                // Mettre à jour le fichier .env
                $this->updateEnvFile('INSTALLATION_LICENSE_KEY', $licenseKey);
                
                Log::info('Clé de licence mise à jour', [
                    'new_key_length' => strlen($licenseKey)
                ]);
                
                // Optimisation : Vider seulement les caches essentiels
                // Éviter config:clear qui est coûteux, utiliser des oublis ciblés
                Cache::forget('license_verification_' . md5($licenseKey));
                Cache::forget('last_license_check_' . md5($licenseKey));
                
                // Vider les sessions liées à la licence
                session()->forget('license_check_session_' . session()->getId());
                session()->forget('license_check_result');
                
                // Recharger la configuration env sans vider tout le cache
                config(['app.installation_license_key' => $licenseKey]);
                
                Log::info('Licence sauvegardée avec succès');
                
                // Vérifier immédiatement la nouvelle licence
                $isValid = $this->licenceService->verifyInstallationLicense();
                
                if ($isValid) {
                    return redirect()->route('admin.settings.license')
                        ->with('success', t('settings_license.license.license_updated_successfully'));
                } else {
                    return redirect()->route('admin.settings.license')
                        ->with('warning', 'Licence sauvegardée mais non valide. Veuillez vérifier votre clé.');
                }
                    
            } catch (\Exception $e) {
                Log::error('Erreur lors de la mise à jour de la licence', [
                    'error' => $e->getMessage()
                ]);
                
                return redirect()->route('admin.settings.license')
                    ->with('error', 'Erreur lors de la sauvegarde: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('admin.settings.license')
            ->with('info', t('settings_license.license.no_changes_made'));
    }
    
    /**
     * Forcer une vérification de licence
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceCheck()
    {
        try {
            // Récupérer la clé de licence actuelle directement depuis le fichier .env
            $licenseKey = $this->getLicenseKeyFromEnv();
            
            // Initialiser les variables pour les détails de licence
            $licenseStatus = 'inconnu';
            $expiryDate = null;
            $isActive = false;
            $isSuspended = false;
            $isRevoked = false;
            $registeredDomain = null;
            $registeredIP = null;
            $lastVerified = null;
            Log::info('Début de vérification forcée de licence', ['license_key' => $licenseKey]);
            
            if (empty($licenseKey)) {
                Log::warning('Aucune clé de licence configurée');
                return redirect()->route('admin.settings.license')
                    ->with('error', t('settings_license.license.no_license_key_configured'));
            }
            
            // Récupérer le domaine et l'adresse IP
            $domain = request()->getHost();
            $ipAddress = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
            
            Log::info('Informations de vérification', [
                'domain' => $domain,
                'ip_address' => $ipAddress
            ]);
            
            // Réinitialiser le cache de session pour forcer une nouvelle vérification
            session()->forget('license_check_session_' . session()->getId());
            session()->forget('license_check_result');
            
            // Vider tous les caches relatifs à la licence
            $cacheKey = 'license_verification_' . md5($licenseKey);
            Cache::forget($cacheKey);
            
            // Utiliser les variables d'environnement pour l'API
            $apiUrl = env('LICENCE_API_URL', 'https://licence.myvcard.fr');
            $apiKey = env('LICENCE_API_KEY', 'sk_wuRFNJ7fI6CaMzJptdfYhzAGW3DieKwC');
            $apiSecret = env('LICENCE_API_SECRET', 'sk_3ewgI2dP0zPyLXlHyDT1qYbzQny6H2hb');
            $endpoint = env('LICENCE_API_ENDPOINT', '/api/check-serial.php');
            
            // Préparer les données à envoyer
            $data = [
                'serial_key' => $licenseKey,
                'domain' => $domain,
                'ip_address' => $ipAddress,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ];
            
            Log::info('Envoi direct de requête API', [
                'url' => $apiUrl . $endpoint,
                'data' => $data
            ]);
            
            // Initialiser cURL
            $ch = curl_init($apiUrl . $endpoint);
            
            // Configurer cURL avec vérification SSL conditionnelle selon l'environnement
            $curlOptions = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5
            ];
            
            // Désactiver la vérification SSL uniquement en environnement local/dev
            if (env('APP_ENV') === 'local' || env('APP_ENV') === 'development') {
                $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
                $curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
                Log::info('Vérification SSL désactivée en environnement local/dev');
            } else {
                // En production, activer la vérification SSL
                $curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
                $curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;
                
                // Spécifier un fichier CA si nécessaire
                if (file_exists(base_path('resources/certs/cacert.pem'))) {
                    $curlOptions[CURLOPT_CAINFO] = base_path('resources/certs/cacert.pem');
                }
            }
            
            curl_setopt_array($ch, $curlOptions);
            
            // Exécuter la requête
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            // Fermer la session cURL
            curl_close($ch);
            
            // DÉBOGAGE DÉTAILLÉ
            Log::alert('DÉBOGAGE - RÉPONSE API BRUTE', [
                'http_code' => $httpCode,
                'response_raw' => $response,
                'error' => $error
            ]);
            
            // Enregistrer la réponse brute pour débogage (s'assurer qu'elle est en format chaîne)
            \App\Models\Setting::set('debug_api_response', is_string($response) ? $response : json_encode($response, JSON_PRETTY_PRINT));
            \App\Models\Setting::set('debug_api_http_code', (string)$httpCode);
            
            $directApiValid = false;
            $apiMessage = t('settings_license.license.api_verification_error');
            
            if ($response !== false) {
                // Décoder la réponse JSON
                $decoded = json_decode($response, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info('Réponse JSON décodée', $decoded);
                    
                    // Extraire les informations de licence, en supportant différents formats de réponse API
                    $licenseData = [];
                    
                    // Logger la réponse complète pour débogage
                    Log::info('Réponse complète de l\'API', $decoded);
                    
                    // Cas 1: API v4 - format data
                    if (isset($decoded['data']) && is_array($decoded['data'])) {
                        $licenseData = $decoded['data'];
                        Log::info('Format API v4 détecté (avec data)');  
                    }
                    // Cas 2: API v1.8.0 - format plat
                    else if (isset($decoded['status'])) {
                        // L'API v1.8.0 peut renvoyer les données directement sans le niveau 'data'
                        $licenseData = $decoded;
                        Log::info('Format API v1.8.0 détecté (format plat)'); 
                    }
                    // Cas 3: Format incomplet ou inconnu
                    else {
                        Log::warning('Format de réponse API inconnu ou incomplet');
                    }
                    
                    // Logger toutes les données de licence pour débogage
                    Log::info('Données de licence extraites', $licenseData);
                    
                    // Déterminer le statut
                    $licenseStatus = 'inconnu';
                    if (isset($licenseData['status'])) {
                        // Format API v4 ou v1.8.0 avec status explicite
                        $licenseStatus = $licenseData['status'];
                    } else if (isset($decoded['success']) && $decoded['success'] === true) {
                        // Format API alternatif, success = true signifie que la licence est active
                        $licenseStatus = 'active';
                    }
                    
                    // Déterminer la date d'expiration - exploration complète
                    $expiryDate = null;
                    $possibleExpiryKeys = [
                        'expires_at', 'expiry_date', 'expire', 'expire_date', 'expiration', 
                        'expiration_date', 'valid_until', 'validity_end', 'end_date', 'end'
                    ];
                    
                    // Chercher dans licenseData d'abord
                    foreach ($possibleExpiryKeys as $key) {
                        if (isset($licenseData[$key]) && !empty($licenseData[$key])) {
                            $expiryDate = $licenseData[$key];
                            Log::info("Date d'expiration trouvée dans licenseData[$key]: " . $expiryDate);
                            break;
                        }
                    }
                    
                    // Si toujours null, chercher dans decoded
                    if ($expiryDate === null) {
                        foreach ($possibleExpiryKeys as $key) {
                            if (isset($decoded[$key]) && !empty($decoded[$key])) {
                                $expiryDate = $decoded[$key];
                                Log::info("Date d'expiration trouvée dans decoded[$key]: " . $expiryDate);
                                break;
                            }
                        }
                    }
                    
                    // Si aucune date trouvée et que la licence est valide, ajouter une date par défaut (1 an)
                    if ($expiryDate === null && (isset($decoded['success']) && $decoded['success'] === true)) {
                        $expiryDate = date('Y-m-d', strtotime('+1 year'));
                        Log::info("Aucune date d'expiration trouvée, utilisation d'une date par défaut: " . $expiryDate);
                    }
                    
                    // Enregistrer pour débogage
                    \App\Models\Setting::set('debug_expiry_date', $expiryDate ?? 'non trouvée');
                    
                    // Récupérer les autres informations
                    $registeredDomain = $licenseData['domain'] ?? null;
                    $registeredIP = $licenseData['ip_address'] ?? null;
                    $lastVerified = $licenseData['last_verified'] ?? null;
                    
                    // Si le statut est valide selon le message mais que nous n'avons pas pu extraire un statut, le définir
                    if ($licenseStatus === 'inconnu' && isset($decoded['success']) && $decoded['success'] === true) {
                        $licenseStatus = 'active';
                    }
                    
                    // Logger les données finales extraites
                    Log::info('Données finales après traitement', [
                        'status' => $licenseStatus,
                        'expiry_date' => $expiryDate,
                        'domain' => $registeredDomain,
                        'ip' => $registeredIP
                    ]);
                        
                    // Déterminer l'état actif/suspendu/révoqué
                    $isActive = $licenseStatus === 'active';
                    $isSuspended = $licenseStatus === 'suspended';
                    $isRevoked = $licenseStatus === 'revoked';
                    
                    // Enregistrer ces informations dans les paramètres
                    \App\Models\Setting::set('license_status', $licenseStatus);
                    \App\Models\Setting::set('license_expiry_date', $expiryDate);
                    \App\Models\Setting::set('license_registered_domain', $registeredDomain);
                    \App\Models\Setting::set('license_registered_ip', $registeredIP);
                    \App\Models\Setting::set('license_last_verified', $lastVerified);
                    
                    if (isset($decoded['status']) && ($decoded['status'] === 'success' || $decoded['status'] === true)) {
                        $directApiValid = true;
                        $apiMessage = $decoded['message'] ?? t('settings_license.license.valid_via_direct_api');
                    } else {
                        $apiMessage = $decoded['message'] ?? t('settings_license.license.invalid_via_direct_api');
                    }
                }
            }
            
            // Test via le service de licence
            $serviceResult = $this->licenceService->validateSerialKey($licenseKey, $domain, $ipAddress);
            Log::info('Résultat du service validateSerialKey', $serviceResult);
            
            // Vérifier la licence via le service complet
            $isValid = $this->licenceService->verifyInstallationLicense();
            Log::info('Résultat du service verifyInstallationLicense', ['valid' => $isValid]);
            
            // Mettre à jour les paramètres
            Setting::set('last_license_check', now()->toDateTimeString());
            Setting::set('license_valid', $isValid);
            
            // Récupérer les informations de licence depuis les paramètres
            $licenseStatus = \App\Models\Setting::get('license_status', 'inconnu');
            $expiryDate = \App\Models\Setting::get('license_expiry_date');
            $registeredDomain = \App\Models\Setting::get('license_registered_domain');
            $registeredIP = \App\Models\Setting::get('license_registered_ip');
            
            // Construire le message avec tous les détails
            $details = [];
            
            if ($licenseStatus) {
                $statusText = '';
                switch ($licenseStatus) {
                    case 'active':
                        $statusText = 'active';
                        break;
                    case 'suspended':
                        $statusText = 'suspendue';
                        break;
                    case 'revoked':
                        $statusText = 'révoquée';
                        break;
                    default:
                        $statusText = $licenseStatus;
                }
                $details[] = t('settings_license.license.status_detail', ['status' => $statusText]);
            }
            
            if ($expiryDate) {
                $expiry = new \DateTime($expiryDate);
                $now = new \DateTime();
                $expired = $expiry < $now;
                
                $expiryText = $expired ? 
                    t('settings_license.license.expired_on', ['date' => $expiry->format('d/m/Y')]) :
                t('settings_license.license.expires_on_date', ['date' => $expiry->format('d/m/Y')]);
                    
                $details[] = t('settings_license.license.expiry_detail', ['expiry' => $expiryText]);
            }
            
            if ($registeredDomain) {
                $details[] = t('settings_license.license.registered_domain', ['domain' => $registeredDomain]);
            }
            
            if ($registeredIP) {
                $details[] = t('settings_license.license.registered_ip', ['ip' => $registeredIP]);
            }
            
            // Message principal simplifié
            $message = '';
            
            if ($isValid) {
                $message = t('settings_license.license.license_valid');
            } else {
                $message = t('settings_license.license.license_invalid');
            }
            
            // Ajouter les détails si disponibles
            if (!empty($details)) {
                $message .= "\n\n" . t('settings_license.license.license_details_header') . "\n" . implode("\n", $details);
            }
            
            // Stocker les informations pour la vue
            session()->flash('license_details', [
                'status' => $licenseStatus,
                'expiry_date' => $expiryDate,
                'registered_domain' => $registeredDomain,
                'registered_ip' => $registeredIP,
                'is_valid' => $isValid
            ]);
            
            return redirect()->route('admin.settings.license')
                ->with($isValid ? 'success' : 'error', $message);
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification forcée de licence: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.settings.license')
                ->with('error', t('settings_license.license.verification_error', ['error' => $e->getMessage()]));
        }
    }
    
    /**
     * Mettre à jour le fichier .env
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    private function updateEnvFile($key, $value)
    {
        $path = base_path('.env');

        // Si le fichier .env n'existe pas, le créer
        if (!File::exists($path)) {
            File::put($path, '');
        }

        // Lire le contenu du fichier
        $content = File::get($path);

        // Échapper les caractères spéciaux dans la valeur
        $escapedValue = addslashes($value);

        // Remplacer la valeur si elle existe déjà
        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
        } else {
            // Ajouter la clé si elle n'existe pas
            $content .= "\n{$key}={$escapedValue}\n";
        }

        // Écrire le contenu mis à jour dans le fichier
        $result = File::put($path, $content);
        
        // Log pour débogage
        Log::info('Fichier .env mis à jour', [
            'key' => $key,
            'value_length' => strlen($value),
            'file_updated' => $result !== false
        ]);
        
        return $result !== false;
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
        
        if (!File::exists($path)) {
            return null;
        }
        
        $content = File::get($path);
        
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
    
    /**
     * Afficher la page de recherche de clés de licence
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        $results = null;
        
        if ($query) {
            $results = SerialKey::where('serial_key', 'like', "%{$query}%")
                ->with('project')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('admin.license-search', compact('results'));
    }
    
    /**
     * Afficher les détails d'une clé de licence (pour l'affichage modal)
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function details($id)
    {
        $key = SerialKey::with(['project', 'histories' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->findOrFail($id);
        
        return View::make('admin.partials.license-details', compact('key'))->render();
    }
    
    /**
     * Suspendre une clé de licence
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend($id)
    {
        $key = SerialKey::findOrFail($id);
        $this->licenceService->suspendKey($key);
        
        return redirect()->back()->with('success', 'La clé de licence a été suspendue avec succès.');
    }
    
    /**
     * Révoquer une clé de licence
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function revoke($id)
    {
        $key = SerialKey::findOrFail($id);
        $this->licenceService->revokeKey($key);
        
        return redirect()->back()->with('success', 'La clé de licence a été révoquée avec succès.');
    }
    
    /**
     * Activer une clé de licence
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($id)
    {
        $key = SerialKey::findOrFail($id);
        $result = $this->licenceService->activateKey($key);
        
        if ($result) {
            return redirect()->back()->with('success', 'La clé de licence a été activée avec succès.');
        } else {
            return redirect()->back()->with('error', 'Impossible d\'activer une clé révoquée.');
        }
    }
}
