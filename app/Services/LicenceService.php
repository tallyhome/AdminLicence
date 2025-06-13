<?php

namespace App\Services;

use App\Models\SerialKey;
use App\Models\Admin;
use App\Models\LicenceHistory;
use App\Notifications\LicenceStatusChanged;
use App\Services\WebSocketService;
use App\Services\LicenceHistoryService;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LicenceService
{
    /**
     * @var WebSocketService
     */
    protected $webSocketService;
    
    /**
     * @var LicenceHistoryService
     */
    protected $historyService;
    
    /**
     * @var EncryptionService
     */
    protected $encryptionService;
    
    /**
     * Constructeur du service de licence
     */
    public function __construct(
        WebSocketService $webSocketService, 
        LicenceHistoryService $historyService,
        EncryptionService $encryptionService
    )
    {
        $this->webSocketService = $webSocketService;
        $this->historyService = $historyService;
        $this->encryptionService = $encryptionService;
    }
    /**
     * Valide une clé de série
     *
     * @param string $serialKey
     * @param string $domain
     * @param string $ipAddress
     * @return array
     */
    public function validateSerialKey(string $serialKey, string $domain, string $ipAddress): array
    {
        // Initialiser le résultat
        $result = [
            'valid' => false,
            'message' => 'Erreur de vérification de licence',
            'data' => []
        ];

        try {
            // Vérifier si le chiffrement est activé
            $useEncryption = env('SECURITY_ENCRYPT_LICENCE_KEYS', true);
            
            // Vérifier d'abord si la clé existe dans la base de données locale
            // Si le chiffrement est activé, essayer de trouver la clé chiffrée ou non chiffrée
            if ($useEncryption) {
                // Essayer de trouver la clé telle quelle (peut-être déjà chiffrée)
                $key = SerialKey::where('serial_key', $serialKey)->first();
                
                // Si non trouvée, essayer de trouver la clé en la chiffrant
                if (!$key) {
                    $encryptedKey = $this->encryptionService->encrypt($serialKey);
                    $key = SerialKey::where('serial_key', $encryptedKey)->first();
                }
            } else {
                // Sans chiffrement, recherche directe
                $key = SerialKey::where('serial_key', $serialKey)->first();
            }
            
            // Si la clé est trouvée localement, utiliser ces informations
            if ($key) {
                // Log uniquement en environnement de développement
                if (env('APP_ENV') === 'local' || env('APP_DEBUG') === true) {
                    Log::debug('Clé trouvée dans la base de données locale', ['key' => $serialKey, 'status' => $key->status]);
                }
                
                // Vérifier si la clé est expirée
                $isExpired = false;
                if ($key->expires_at) {
                    $expiryDate = \Carbon\Carbon::parse($key->expires_at);
                    $isExpired = $expiryDate->isPast();
                }
                
                // Déterminer la validité de la clé
                $isValid = $key->status === 'active' && !$isExpired;
                
                // Mettre à jour le domaine et l'adresse IP si nécessaire
                if ($isValid && $domain && $ipAddress) {
                    $key->domain = $domain;
                    $key->ip_address = $ipAddress;
                    
                    // Si le chiffrement est activé et que la clé n'est pas encore chiffrée
                    if (env('SECURITY_ENCRYPT_LICENCE_KEYS', true) && !$this->encryptionService->isEncrypted($key->serial_key)) {
                        $key->serial_key = $this->encryptionService->encrypt($key->serial_key);
                    }
                    
                    $key->save();
                    
                    // Enregistrer l'utilisation dans l'historique
                    $this->historyService->logAction($key, 'verify', [
                        'domain' => $domain,
                        'ip_address' => $ipAddress,
                        'timestamp' => now()->toDateTimeString(),
                        'success' => true
                    ]);
                } else {
                    // Enregistrer l'échec de validation dans l'historique
                    if ($key) {
                        $this->historyService->logAction($key, 'verify_failed', [
                            'domain' => $domain,
                            'ip_address' => $ipAddress,
                            'timestamp' => now()->toDateTimeString(),
                            'reason' => $isExpired ? 'expired' : 'invalid_status',
                            'success' => false
                        ]);
                    }
                }
                
                // Générer le message approprié
                $message = 'Clé de série ';
                if ($isExpired) {
                    $message .= 'expirée';
                } elseif ($key->status === 'suspended') {
                    $message .= 'suspendue';
                } elseif ($key->status === 'revoked') {
                    $message .= 'révoquée';
                } elseif ($isValid) {
                    $message .= 'valide';
                } else {
                    $message .= 'invalide';
                }
                
                // Formater la date d'expiration
                $formattedDate = null;
                if ($key->expires_at) {
                    try {
                        $formattedDate = \Carbon\Carbon::parse($key->expires_at)->format('d/m/Y');
                    } catch (\Exception $e) {
                        $formattedDate = $key->expires_at;
                    }
                }
                
                // Générer un token sécurisé avec HMAC-SHA256 et expiration
                $token = $this->generateSecureToken($serialKey, $domain, $ipAddress);
                
                // Stocker le token dans le cache avec une expiration
                $tokenExpiry = env('SECURITY_TOKEN_EXPIRY_MINUTES', 60);
                Cache::put('licence_token_' . $key->id, $token, now()->addMinutes($tokenExpiry));
                
                return [
                    'valid' => $isValid,
                    'message' => $message,
                    'token' => $token,
                    'project' => $key->project ? $key->project->name : 'AdminLicence',
                    'expires_at' => $formattedDate,
                    'status' => $key->status,
                    'is_expired' => $isExpired,
                    'is_suspended' => $key->status === 'suspended',
                    'is_revoked' => $key->status === 'revoked',
                    'status_code' => $isValid ? 200 : 401,
                    'token_expires_in' => $tokenExpiry * 60 // en secondes
                ];
            }
            
            // Si la clé n'est pas trouvée localement, essayer avec l'API externe
            // Configuration de l'API de licence depuis les variables d'environnement
            $apiUrl = env('LICENCE_API_URL', 'https://licence.myvcard.fr');
            $apiKey = env('LICENCE_API_KEY', '');
            $apiSecret = env('LICENCE_API_SECRET', '');
            $endpoint = env('LICENCE_API_ENDPOINT', '/api/check-serial.php'); // Utiliser le même point d'entrée que le script d'installation
            
            // Préparer les données à envoyer
            $data = [
                'serial_key' => $serialKey,
                'domain' => $domain,
                'ip_address' => $ipAddress,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ];
            
            // Logger la requête uniquement en environnement de développement
            if (env('APP_ENV') === 'local' || env('APP_DEBUG') === true) {
                Log::debug('Envoi de requête de vérification de licence', [
                    'url' => $apiUrl . $endpoint,
                    'data' => $data
                ]);
            }
            
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
            $info = curl_getinfo($ch);
            
            // Fermer la session cURL
            curl_close($ch);
            
            // Logger la réponse uniquement en environnement de développement
            if (env('APP_ENV') === 'local' || env('APP_DEBUG') === true) {
                Log::debug('Réponse API de licence', [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error' => $error,
                    'info' => $info
                ]);
            }
            
            // Vérifier si la requête a échoué
            if ($response === false) {
                Log::error('Erreur cURL lors de la vérification de licence: ' . $error);
                return [
                    'valid' => false,
                    'message' => 'Erreur de connexion au serveur de licence: ' . $error,
                    'data' => [],
                    'api_error' => $error
                ];
            }
            
            // Décoder la réponse JSON
            $decoded = json_decode($response, true);
            
            // Vérifier si le décodage a échoué
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erreur de décodage JSON: ' . json_last_error_msg() . ' - Réponse: ' . substr($response, 0, 1000));
                
                // Vérifier si la réponse contient des mots-clés positifs
                if ($httpCode == 200 && (strpos($response, 'success') !== false || strpos($response, 'valid') !== false)) {
                    Log::info('Licence valide (réponse non-JSON)!');
                    
                    return [
                        'valid' => true,
                        'message' => 'Licence valide',
                        'data' => [
                            'expiry_date' => date('Y-m-d', strtotime('+1 year')),
                            'token' => $this->generateSecureToken($serialKey, $domain, $ipAddress),
                            'project' => 'AdminLicence'
                        ]
                    ];
                }
                
                return [
                    'valid' => false,
                    'message' => 'Erreur de décodage de la réponse du serveur de licence',
                    'data' => []
                ];
            }
            
            // Vérifier si la licence est valide selon le format de réponse du script d'installation
            if ($httpCode == 200 && isset($decoded['status'])) {
                if ($decoded['status'] === 'success' || $decoded['status'] === true) {
                    // Log uniquement en environnement de développement ou en cas d'erreur
                    if (env('APP_ENV') === 'local' || env('APP_DEBUG') === true) {
                        Log::info('Licence valide!', ['response' => $decoded]);
                    }
                    
                    return [
                        'valid' => true,
                        'message' => $decoded['message'] ?? 'Licence valide',
                        'data' => [
                            'expiry_date' => $decoded['expiry_date'] ?? ($decoded['data']['expiry_date'] ?? null),
                            'token' => $decoded['token'] ?? ($decoded['data']['token'] ?? null),
                            'project' => $decoded['project'] ?? ($decoded['data']['project'] ?? null)
                        ]
                    ];
                } else {
                    Log::warning('Licence invalide: ' . ($decoded['message'] ?? 'Raison inconnue'));
                    
                    return [
                        'valid' => false,
                        'message' => $decoded['message'] ?? 'Licence invalide',
                        'data' => []
                    ];
                }
            }
            
            // Si on arrive ici, la réponse n'est pas dans un format attendu
            Log::error('Format de réponse inattendu', ['response' => $decoded]);
            
            return [
                'valid' => false,
                'message' => 'Format de réponse inattendu du serveur de licence',
                'data' => []
            ];
        } catch (\Exception $e) {
            // Logger l'exception
            Log::error('Exception lors de la vérification de licence: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            // Retourner une erreur
            return [
                'valid' => false,
                'message' => 'Erreur lors de la vérification de licence: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Suspendre une clé de série.
     *
     * @param SerialKey $serialKey
     * @return void
     */
    public function suspendKey(SerialKey $serialKey): void
    {
        $serialKey->update([
            'status' => 'suspended'
        ]);

        // Notifier le propriétaire du projet
        if ($serialKey->project->user) {
            $serialKey->project->user->notify(new LicenceStatusChanged($serialKey, 'suspended'));
        }
        
        // Envoyer une notification WebSocket aux administrateurs
        $this->webSocketService->notifyLicenceStatusChange($serialKey, 'suspended');
    }

    /**
     * Révoquer une clé de série.
     *
     * @param SerialKey $serialKey
     * @return void
     */
    public function revokeKey(SerialKey $serialKey): void
    {
        $serialKey->update([
            'status' => 'revoked'
        ]);

        // Notifier le propriétaire du projet
        if ($serialKey->project->user) {
            $serialKey->project->user->notify(new LicenceStatusChanged($serialKey, 'revoked'));
        }
        
        // Enregistrement des modifications dans le journal
        Log::info('Clé de licence révoquée', [
            'serial_key' => $serialKey->serial_key,
            'project_id' => $serialKey->project_id,
            'domain' => $serialKey->domain
        ]);
    }

    /**
     * Génère un token sécurisé pour l'authentification API
     * 
     * @param string $serialKey
     * @param string $domain
     * @param string $ipAddress
     * @return string
     */
    public function generateSecureToken(string $serialKey, string $domain, string $ipAddress): string
    {
        // Utiliser HMAC-SHA256 au lieu de MD5 pour une meilleure sécurité
        $secret = env('SECURITY_TOKEN_SECRET', 'default_secret_change_me');
        $expiryTime = time() + (env('SECURITY_TOKEN_EXPIRY_MINUTES', 60) * 60);
        $data = $serialKey . '|' . $domain . '|' . $ipAddress . '|' . $expiryTime;
        
        return hash_hmac('sha256', $data, $secret);
    }

    /**
     * Génère une nouvelle clé de licence unique
     *
     * @return string
     */
    public function generateKey(): string
    {
        do {
            $key = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (SerialKey::where('serial_key', $key)->exists());

        return $key;
    }
    
    /**
     * Vérifie la licence d'installation
     *
     * @return bool
     */
    public function verifyInstallationLicense(): bool
    {
        try {
            // Récupérer la clé de licence directement depuis le fichier .env
            $licenseKey = $this->getLicenseKeyFromEnv();
            
            if (empty($licenseKey)) {
                Log::warning('Aucune clé de licence configurée');
                return false;
            }
            
            // Commenté : Ne plus autoriser l'accès automatique en environnement local
            // La licence doit être valide dans tous les environnements
            // if (env('APP_ENV') === 'local' && env('APP_DEBUG') === true) {
            //     Log::info('Environnement local avec debug activé, licence considérée comme valide');
            //     return true;
            // }
            
            // Vérifier si nous avons un résultat en cache récent
            $cacheKey = 'license_verification_' . md5($licenseKey);
            $lastCheckKey = 'last_license_check_' . md5($licenseKey);
            
            $cachedResult = Cache::get($cacheKey);
            $lastCheck = Cache::get($lastCheckKey, 0);
            $currentTime = time();
            
            // Utiliser le cache si disponible et récent (moins de 4 heures)
            $fourHoursInSeconds = 4 * 60 * 60;
            if ($cachedResult !== null && ($currentTime - $lastCheck) < $fourHoursInSeconds) {
                Log::info('Utilisation du cache pour la vérification de licence', ['result' => $cachedResult]);
                return $cachedResult;
            }
            
            // Récupérer le domaine et l'adresse IP
            $domain = request()->getHost() ?? 'localhost';
            $ipAddress = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
            
            Log::info('Vérification de licence en cours', [
                'domain' => $domain,
                'ip' => $ipAddress
            ]);
            
            // Appeler la méthode de validation avec timeout réduit
            $result = $this->validateSerialKey($licenseKey, $domain, $ipAddress);
            
            $isValid = $result['valid'] === true;
            
            // Mettre en cache le résultat et le timestamp
            Cache::put($cacheKey, $isValid, 60 * 24); // 24 heures
            Cache::put($lastCheckKey, $currentTime, 60 * 24 * 7); // 7 jours
            
            if ($isValid) {
                Log::info('Licence validée avec succès');
            } else {
                Log::warning('Licence invalide', ['message' => $result['message'] ?? 'Aucun détail']);
            }
            
            return $isValid;
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de licence', [
                'message' => $e->getMessage()
            ]);
            
            // Commenté : Ne plus autoriser l'accès automatique en cas d'erreur en environnement local
            // La licence doit être valide dans tous les environnements
            // if (env('APP_ENV') === 'local') {
            //     Log::warning('Erreur de vérification en environnement local, licence considérée comme valide');
            //     return true;
            // }
            
            // En cas d'erreur, utiliser le dernier résultat en cache si disponible
            $cacheKey = 'license_verification_' . md5($this->getLicenseKeyFromEnv() ?? '');
            $cachedResult = Cache::get($cacheKey);
            if ($cachedResult !== null) {
                Log::warning('Utilisation du cache en cas d\'erreur', ['cached_result' => $cachedResult]);
                return $cachedResult;
            }
            
            return false;
        }
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