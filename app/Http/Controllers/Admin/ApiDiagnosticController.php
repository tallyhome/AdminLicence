<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use App\Models\SerialKey;
use App\Models\Project;
use App\Services\LicenceService;

class ApiDiagnosticController extends Controller
{
    protected $licenceService;
    
    public function __construct(LicenceService $licenceService)
    {
        $this->licenceService = $licenceService;
    }
    
    /**
     * Affiche la page de diagnostic API
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupérer les informations sur le serveur
        $serverInfo = $this->getServerInfo();
        
        // Récupérer les statistiques de la base de données
        $dbStats = $this->getDatabaseStats();
        
        // Récupérer les dernières entrées de log
        $logEntries = $this->getLatestLogEntries();
        
        // Récupérer les permissions des fichiers critiques
        $filePermissions = $this->getFilePermissions();
        
        // Récupérer quelques clés de série pour les tests
        $serialKeys = SerialKey::with('project')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // URL de l'outil de diagnostic API
        $apiDiagnosticUrl = url('/api-diagnostic.php');
        
        return view('admin.settings.api-diagnostic', compact(
            'serverInfo',
            'dbStats',
            'logEntries',
            'filePermissions',
            'serialKeys',
            'apiDiagnosticUrl'
        ));
    }
    
    /**
     * Teste la validité d'une clé de série
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testSerialKey(Request $request)
    {
        $request->validate([
            'serial_key' => 'required|string',
            'domain' => 'nullable|string',
            'ip_address' => 'nullable|string'
        ]);
        
        try {
            $serialKey = $request->input('serial_key');
            $domain = $request->input('domain') ?: request()->getHost();
            $ipAddress = $request->input('ip_address') ?: request()->ip();
            
            $result = $this->licenceService->validateSerialKey($serialKey, $domain, $ipAddress);
            
            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du test de clé de série', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Teste la connexion à l'API externe
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testApiConnection(Request $request)
    {
        try {
            $apiUrl = 'https://licence.myvcard.fr/api/check-serial.php';
            $testData = [
                'serial_key' => 'TEST-CONN-TION-TEST',
                'domain' => request()->getHost(),
                'ip_address' => request()->ip(),
                'api_key' => 'sk_wuRFNJ7fI6CaMzJptdfYhzAGW3DieKwC',
                'api_secret' => 'sk_3ewgI2dP0zPyLXlHyDT1qYbzQny6H2hb'
            ];
            
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($apiUrl, $testData);
            
            $statusCode = $response->status();
            $responseBody = $response->json() ?: $response->body();
            
            return response()->json([
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du test de connexion API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Teste la connexion à la base de données
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testDatabaseConnection()
    {
        try {
            $dbConfig = config('database.connections.' . config('database.default'));
            $testConnection = DB::connection()->getPdo();
            
            $dbStats = $this->getDatabaseStats();
            
            return response()->json([
                'success' => true,
                'message' => 'Connexion à la base de données établie',
                'driver' => $dbConfig['driver'],
                'database' => $dbConfig['database'],
                'version' => DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
                'stats' => $dbStats
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du test de connexion à la base de données', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Vérifie les permissions des fichiers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPermissions()
    {
        try {
            $filePermissions = $this->getFilePermissions();
            
            $allWritable = true;
            foreach ($filePermissions as $permission) {
                if (!$permission['writable'] && $permission['should_be_writable']) {
                    $allWritable = false;
                    break;
                }
            }
            
            return response()->json([
                'success' => $allWritable,
                'message' => $allWritable ? 'Toutes les permissions sont correctes' : 'Certains fichiers ou dossiers ont des permissions incorrectes',
                'permissions' => $filePermissions
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des permissions', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification des permissions : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les dernières entrées de log
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLatestLogs(Request $request)
    {
        try {
            $logEntries = $this->getLatestLogEntries($request->input('lines', 50));
            
            return response()->json([
                'success' => true,
                'logs' => $logEntries
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des logs', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des logs : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les informations sur le serveur
     *
     * @return array
     */
    protected function getServerInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS,
            'database' => config('database.default'),
            'timezone' => config('app.timezone'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'extensions' => [
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'gd' => extension_loaded('gd')
            ]
        ];
    }
    
    /**
     * Récupère les statistiques de la base de données
     *
     * @return array
     */
    protected function getDatabaseStats()
    {
        try {
            return [
                'serial_keys' => SerialKey::count(),
                'projects' => Project::count(),
                'admins' => DB::table('admins')->count(),
                'active_keys' => SerialKey::where('status', 'active')->count(),
                'expired_keys' => SerialKey::where('status', 'expired')->count(),
                'revoked_keys' => SerialKey::where('status', 'revoked')->count(),
                'suspended_keys' => SerialKey::where('status', 'suspended')->count()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques de la base de données', [
                'message' => $e->getMessage()
            ]);
            
            return [
                'error' => 'Impossible de récupérer les statistiques : ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupère les dernières entrées de log
     *
     * @param int $lines Nombre de lignes à récupérer
     * @return array
     */
    protected function getLatestLogEntries($lines = 20)
    {
        $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
        $entries = [];
        
        if (File::exists($logFile)) {
            try {
                $process = new Process(['tail', '-n', $lines, $logFile]);
                $process->run();
                
                if ($process->isSuccessful()) {
                    $output = $process->getOutput();
                    $logEntries = preg_split('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $output, -1, PREG_SPLIT_DELIM_CAPTURE);
                    
                    // Ignorer le premier élément vide
                    array_shift($logEntries);
                    
                    // Regrouper les entrées par paires (date + contenu)
                    for ($i = 0; $i < count($logEntries); $i += 2) {
                        if (isset($logEntries[$i]) && isset($logEntries[$i+1])) {
                            $entries[] = [
                                'timestamp' => $logEntries[$i],
                                'content' => trim($logEntries[$i+1])
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de la récupération des logs', [
                    'message' => $e->getMessage()
                ]);
            }
        }
        
        return $entries;
    }
    
    /**
     * Récupère les permissions des fichiers critiques
     *
     * @return array
     */
    protected function getFilePermissions()
    {
        $pathsToCheck = [
            [
                'path' => storage_path(),
                'name' => 'storage',
                'should_be_writable' => true
            ],
            [
                'path' => base_path('bootstrap/cache'),
                'name' => 'bootstrap/cache',
                'should_be_writable' => true
            ],
            [
                'path' => public_path(),
                'name' => 'public',
                'should_be_writable' => true
            ],
            [
                'path' => public_path('api'),
                'name' => 'public/api',
                'should_be_writable' => false
            ],
            [
                'path' => base_path('.env'),
                'name' => '.env',
                'should_be_writable' => true
            ],
            [
                'path' => base_path('vendor'),
                'name' => 'vendor',
                'should_be_writable' => false
            ]
        ];
        
        $permissions = [];
        foreach ($pathsToCheck as $item) {
            $path = $item['path'];
            $permissions[] = [
                'name' => $item['name'],
                'path' => $path,
                'exists' => File::exists($path),
                'writable' => is_writable($path),
                'should_be_writable' => $item['should_be_writable'],
                'permissions' => File::exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A',
                'type' => is_dir($path) ? 'directory' : 'file'
            ];
        }
        
        return $permissions;
    }
}
