<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SerialKey;
use App\Models\Setting;
use App\Services\LicenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

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
        // Récupérer la clé de licence actuelle depuis le fichier .env
        $licenseKey = env('INSTALLATION_LICENSE_KEY');
        
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
            'check_frequency' => 'required|integer|min:1|max:100',
            'license_key' => 'nullable|string|max:255'
        ]);
        
        // Mettre à jour la fréquence de vérification
        Setting::set('license_check_frequency', $request->input('check_frequency'), 'Fréquence de vérification de licence');
        
        // Mettre à jour la clé de licence dans le fichier .env si fournie
        $licenseKey = $request->input('license_key');
        if (!empty($licenseKey)) {
            $this->updateEnvFile('INSTALLATION_LICENSE_KEY', $licenseKey);
            
            // Réinitialiser le cache de session pour forcer une nouvelle vérification
            session()->forget('license_check_session_' . session()->getId());
            session()->forget('license_check_result');
            
            // Forcer une vérification immédiate
            $isValid = $this->licenceService->verifyInstallationLicense();
            Setting::set('license_valid', $isValid);
            Setting::set('last_license_check', now()->toDateTimeString());
            
            return redirect()->route('admin.settings.license')
                ->with('success', 'La clé de licence et les paramètres de vérification ont été mis à jour avec succès.');
        }
        
        return redirect()->route('admin.settings.license')
            ->with('success', 'Les paramètres de vérification de licence ont été mis à jour avec succès.');
    }
    
    /**
     * Forcer une vérification de licence
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceCheck()
    {
        try {
            // Récupérer la clé de licence actuelle
            $licenseKey = env('INSTALLATION_LICENSE_KEY');
            
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
                    ->with('error', 'Aucune clé de licence n\'est configurée dans le fichier .env.');
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
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
            
            // Test direct de l'API sans passer par le service
            $apiUrl = 'https://licence.myvcard.fr';
            $apiKey = 'sk_wuRFNJ7fI6CaMzJptdfYhzAGW3DieKwC';
            $apiSecret = 'sk_3ewgI2dP0zPyLXlHyDT1qYbzQny6H2hb';
            $endpoint = '/api/check-serial.php';
            
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
            
            // Configurer cURL
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5
            ]);
            
            // Exécuter la requête
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            // Fermer la session cURL
            curl_close($ch);
            
            Log::info('Réponse API directe', [
                'http_code' => $httpCode,
                'response' => $response,
                'error' => $error
            ]);
            
            $directApiValid = false;
            $apiMessage = 'Erreur lors de la vérification directe de l\'API';
            
            if ($response !== false) {
                // Décoder la réponse JSON
                $decoded = json_decode($response, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info('Réponse JSON décodée', $decoded);
                    
                    if (isset($decoded['status']) && ($decoded['status'] === 'success' || $decoded['status'] === true)) {
                        $directApiValid = true;
                        $apiMessage = $decoded['message'] ?? 'Licence valide via API directe';
                    } else {
                        $apiMessage = $decoded['message'] ?? 'Licence invalide via API directe';
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
            
            $message = '';
            
            if ($isValid) {
                $message = 'La licence est valide.';
            } else {
                if ($directApiValid) {
                    $message = 'L\'API indique que la licence est valide, mais le service de licence la considère comme invalide. Problème de configuration potentiel.';
                } else {
                    $message = 'La licence n\'est pas valide selon l\'API et le service. Message API: ' . $apiMessage;
                }
            }
            
            return redirect()->route('admin.settings.license')
                ->with($isValid ? 'success' : 'error', $message);
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification forcée de licence: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.settings.license')
                ->with('error', 'Une erreur est survenue lors de la vérification de la licence: ' . $e->getMessage());
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

        // Remplacer la valeur si elle existe déjà
        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            // Ajouter la clé si elle n'existe pas
            $content .= "\n{$key}={$value}\n";
        }

        // Écrire le contenu mis à jour dans le fichier
        File::put($path, $content);
        
        // Vider le cache de configuration
        if (function_exists('config:clear')) {
            \Artisan::call('config:clear');
        }
        
        return true;
    }
}
