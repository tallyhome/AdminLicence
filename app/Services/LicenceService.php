<?php

namespace App\Services;

use App\Models\SerialKey;
use App\Models\Admin;
use App\Notifications\LicenceStatusChanged;
use App\Services\WebSocketService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenceService
{
    /**
     * @var WebSocketService
     */
    protected $webSocketService;
    
    /**
     * Constructeur du service de licence
     */
    public function __construct(WebSocketService $webSocketService)
    {
        $this->webSocketService = $webSocketService;
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
            // Vérifier d'abord si la clé existe dans la base de données locale
            $key = SerialKey::where('serial_key', $serialKey)->first();
            
            // Si la clé est trouvée localement, utiliser ces informations
            if ($key) {
                Log::info('Clé trouvée dans la base de données locale', ['key' => $serialKey, 'status' => $key->status]);
                
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
                    $key->save();
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
                
                return [
                    'valid' => $isValid,
                    'message' => $message,
                    'token' => md5($serialKey . $domain . $ipAddress . time()),
                    'project' => $key->project ? $key->project->name : 'AdminLicence',
                    'expires_at' => $formattedDate,
                    'status' => $key->status,
                    'is_expired' => $isExpired,
                    'is_suspended' => $key->status === 'suspended',
                    'is_revoked' => $key->status === 'revoked',
                    'status_code' => $isValid ? 200 : 401
                ];
            }
            
            // Si la clé n'est pas trouvée localement, essayer avec l'API externe
            // Configuration de l'API de licence
            $apiUrl = 'https://licence.myvcard.fr';
            $apiKey = 'sk_wuRFNJ7fI6CaMzJptdfYhzAGW3DieKwC';
            $apiSecret = 'sk_3ewgI2dP0zPyLXlHyDT1qYbzQny6H2hb';
            $endpoint = '/api/check-serial.php'; // Utiliser le même point d'entrée que le script d'installation
            
            // Préparer les données à envoyer
            $data = [
                'serial_key' => $serialKey,
                'domain' => $domain,
                'ip_address' => $ipAddress,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ];
            
            // Logger la requête pour le débogage
            Log::debug('Envoi de requête de vérification de licence', [
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
                CURLOPT_SSL_VERIFYPEER => false, // Désactiver pour le débogage
                CURLOPT_SSL_VERIFYHOST => 0,     // Désactiver pour le débogage
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5
            ]);
            
            // Exécuter la requête
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            
            // Fermer la session cURL
            curl_close($ch);
            
            // Logger la réponse pour le débogage
            Log::debug('Réponse API de licence', [
                'http_code' => $httpCode,
                'response' => $response,
                'error' => $error,
                'info' => $info
            ]);
            
            // Vérifier si la requête a échoué
            if ($response === false) {
                Log::error('Erreur cURL lors de la vérification de licence: ' . $error);
                return [
                    'valid' => false,
                    'message' => 'Erreur de connexion au serveur de licence: ' . $error,
                    'data' => []
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
                            'token' => md5($serialKey . $domain . $ipAddress . time()),
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
                    Log::info('Licence valide!', ['response' => $decoded]);
                    
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
        
        // Envoyer une notification WebSocket aux administrateurs
        $this->webSocketService->notifyLicenceStatusChange($serialKey, 'revoked');
    }

    /**
     * Activer une clé de série.
     *
     * @param SerialKey $serialKey
     * @return bool
     */
    public function activateKey(SerialKey $serialKey): bool
    {
        if ($serialKey->status === 'revoked') {
            return false;
        }

        $serialKey->update([
            'status' => 'active'
        ]);

        // Notifier le propriétaire du projet
        if ($serialKey->project->user) {
            $serialKey->project->user->notify(new LicenceStatusChanged($serialKey, 'active'));
        }
        
        // Envoyer une notification WebSocket aux administrateurs
        $this->webSocketService->notifyLicenceStatusChange($serialKey, 'activated');

        return true;
    }

    /**
     * Générer un code sécurisé pour une clé de série.
     *
     * @param string $serialKey
     * @param string $token
     * @return array
     */
    public function generateSecureCode(string $serialKey, string $token): array
    {
        $key = SerialKey::where('serial_key', $serialKey)->first();

        if (!$key) {
            return [
                'success' => false,
                'message' => 'Clé de série invalide'
            ];
        }

        $secureCode = $this->createSecureCode($key->id);

        // Stocker le code dans le cache pendant 5 minutes
        Cache::put("secure_code_{$key->id}", $secureCode, 300);

        return [
            'success' => true,
            'secure_code' => $secureCode
        ];
    }

    /**
     * Créer un code sécurisé pour une clé de série.
     *
     * @param int $keyId
     * @return string
     */
    private function createSecureCode(int $keyId): string
    {
        return hash('sha256', $keyId . time() . Str::random(32));
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
            // Récupérer la clé de licence d'installation depuis les paramètres
            $licenseKey = env('INSTALLATION_LICENSE_KEY');
            
            // Journaliser le début de la vérification
            Log::info('Début de vérification de licence', [
                'license_key' => $licenseKey,
                'app_env' => env('APP_ENV', 'production')
            ]);
            
            // Vérifier si une clé de licence est configurée
            if (empty($licenseKey)) {
                Log::warning('Clé de licence d\'installation non configurée');
                return false; // Bloquer l'accès si aucune licence n'est configurée
            }
            
            // Forcer le rafraîchissement du cache si demandé
            $forceRefresh = request()->has('force_license_check');
            
            // Vérifier si le résultat est en cache et qu'on ne force pas le rafraîchissement
            $cacheKey = 'license_verification_' . md5($licenseKey);
            if (!$forceRefresh && Cache::has($cacheKey)) {
                $cachedResult = Cache::get($cacheKey);
                Log::info('Résultat de vérification de licence récupéré du cache', [
                    'valid' => $cachedResult,
                    'key' => $licenseKey
                ]);
                return $cachedResult;
            }
            
            // Récupérer le domaine actuel
            $domain = request()->getHost();
            
            // Récupérer l'adresse IP du serveur
            $ipAddress = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
            
            Log::info('Paramètres de vérification d\'API', [
                'license_key' => $licenseKey,
                'domain' => $domain,
                'ip_address' => $ipAddress
            ]);
            
            // Vérifier la validité de la licence via l'API externe
            $result = $this->validateSerialKey($licenseKey, $domain, $ipAddress);
            $isValid = $result['valid'] === true;
            
            Log::info('Résultat de vérification de licence via API', [
                'valid' => $isValid,
                'message' => $result['message'] ?? 'Aucun message',
                'data' => $result['data'] ?? []
            ]);
            
            // Mettre en cache le résultat pendant 24 heures
            Cache::put($cacheKey, $isValid, 60 * 24);
            
            return $isValid;
        } catch (\Exception $e) {
            // En cas d'erreur (serveur indisponible, etc.), logger l'erreur
            Log::error('Erreur lors de la vérification de licence', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Bloquer l'accès en cas d'erreur pour des raisons de sécurité
            return false;
        }
    }
}