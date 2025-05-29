<?php
/**
 * Outil de diagnostic API unifié pour AdminLicence
 * Cet outil permet de tester toutes les fonctionnalités API en un seul endroit
 */

// Initialisation
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Protection par mot de passe (à modifier)
$username = 'admin';
$password = 'AdminLicence2025';

// Vérification de l'authentification
if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== $username || 
    $_SERVER['PHP_AUTH_PW'] !== $password) {
    header('WWW-Authenticate: Basic realm="API Diagnostic Tool"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentification requise pour accéder à cet outil'
    ]);
    exit;
}

// Traitement de la requête
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Récupération des services nécessaires
$licenceService = $app->make(\App\Services\LicenceService::class);

// Définir le type de contenu par défaut
header('Content-Type: application/json');

// Fonction pour obtenir l'URL de base
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . '://' . $host;
}

// Fonction pour obtenir les informations du serveur
function getServerInfo() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'os' => PHP_OS,
        'laravel_version' => app()->version(),
        'database' => config('database.default'),
        'timezone' => config('app.timezone'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize')
    ];
}

// Traitement des actions
$action = $_GET['action'] ?? 'info';
$result = [];

switch ($action) {
    case 'info':
        // Informations générales sur l'API
        $result = [
            'status' => 'success',
            'message' => 'API AdminLicence',
            'version' => '1.8.0',
            'server_info' => getServerInfo(),
            'endpoints' => [
                'check_serial' => getBaseUrl() . '/api/check-serial.php',
                'v1_check_serial' => getBaseUrl() . '/api/v1/check-serial.php'
            ],
            'available_actions' => [
                'info' => 'Informations générales sur l\'API',
                'test_serial' => 'Tester une clé de série',
                'test_connection' => 'Tester la connexion à l\'API',
                'test_database' => 'Tester la connexion à la base de données',
                'check_permissions' => 'Vérifier les permissions des fichiers',
                'view_logs' => 'Afficher les dernières entrées de log'
            ]
        ];
        break;
        
    case 'test_serial':
        // Tester une clé de série
        $serialKey = $_POST['serial_key'] ?? $_GET['serial_key'] ?? '';
        $domain = $_POST['domain'] ?? $_GET['domain'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $ipAddress = $_POST['ip_address'] ?? $_GET['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        if (empty($serialKey)) {
            $result = [
                'status' => 'error',
                'message' => 'Le paramètre serial_key est requis'
            ];
        } else {
            try {
                $validationResult = $licenceService->validateSerialKey($serialKey, $domain, $ipAddress);
                $result = [
                    'status' => $validationResult['valid'] ? 'success' : 'error',
                    'message' => $validationResult['message'],
                    'data' => $validationResult
                ];
            } catch (\Exception $e) {
                $result = [
                    'status' => 'error',
                    'message' => 'Erreur lors de la validation: ' . $e->getMessage()
                ];
            }
        }
        break;
        
    case 'test_connection':
        // Tester la connexion à l'API externe
        $apiUrl = 'https://licence.myvcard.fr/api/check-serial.php';
        $testData = [
            'serial_key' => 'TEST-CONN-TION-TEST',
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];
        
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($testData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $result = [
            'status' => $httpCode >= 200 && $httpCode < 300 ? 'success' : 'error',
            'message' => $error ? 'Erreur de connexion: ' . $error : 'Connexion établie avec le code HTTP ' . $httpCode,
            'data' => [
                'http_code' => $httpCode,
                'response' => $response ? json_decode($response, true) : null,
                'error' => $error
            ]
        ];
        break;
        
    case 'test_database':
        // Tester la connexion à la base de données
        try {
            $dbConfig = config('database.connections.' . config('database.default'));
            $testConnection = DB::connection()->getPdo();
            
            $result = [
                'status' => 'success',
                'message' => 'Connexion à la base de données établie',
                'data' => [
                    'driver' => $dbConfig['driver'],
                    'database' => $dbConfig['database'],
                    'version' => DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION),
                    'tables' => [
                        'serial_keys' => DB::table('serial_keys')->count(),
                        'projects' => DB::table('projects')->count(),
                        'admins' => DB::table('admins')->count()
                    ]
                ]
            ];
        } catch (\Exception $e) {
            $result = [
                'status' => 'error',
                'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()
            ];
        }
        break;
        
    case 'check_permissions':
        // Vérifier les permissions des fichiers
        $pathsToCheck = [
            'storage' => storage_path(),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            'public' => public_path(),
            'public/api' => public_path('api'),
            '.env' => base_path('.env')
        ];
        
        $permissionsData = [];
        foreach ($pathsToCheck as $name => $path) {
            $permissionsData[$name] = [
                'path' => $path,
                'exists' => file_exists($path),
                'writable' => is_writable($path),
                'permissions' => file_exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A'
            ];
        }
        
        $result = [
            'status' => 'success',
            'message' => 'Vérification des permissions terminée',
            'data' => $permissionsData
        ];
        break;
        
    case 'view_logs':
        // Afficher les dernières entrées de log
        $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
        $logLines = [];
        
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $logEntries = preg_split('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $logContent, -1, PREG_SPLIT_DELIM_CAPTURE);
            
            // Ignorer le premier élément vide
            array_shift($logEntries);
            
            // Regrouper les entrées par paires (date + contenu)
            for ($i = 0; $i < count($logEntries); $i += 2) {
                if (isset($logEntries[$i]) && isset($logEntries[$i+1])) {
                    $logLines[] = [
                        'timestamp' => $logEntries[$i],
                        'content' => trim($logEntries[$i+1])
                    ];
                }
            }
            
            // Limiter aux 20 dernières entrées
            $logLines = array_slice($logLines, -20);
        }
        
        $result = [
            'status' => 'success',
            'message' => 'Dernières entrées de log',
            'data' => [
                'log_file' => $logFile,
                'entries' => $logLines
            ]
        ];
        break;
        
    default:
        $result = [
            'status' => 'error',
            'message' => 'Action non reconnue: ' . $action,
            'available_actions' => [
                'info', 'test_serial', 'test_connection', 
                'test_database', 'check_permissions', 'view_logs'
            ]
        ];
}

// Afficher le résultat
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Terminer la requête
$kernel->terminate($request, $response);
