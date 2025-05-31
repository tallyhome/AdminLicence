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
use App\Models\Admin;
use App\Services\LicenceService;
use Carbon\Carbon;

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
     * @return \Illuminate\Http\RedirectResponse
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
            
            // Désactiver la vérification SSL pour le test uniquement
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->withoutVerifying() // Désactive la vérification du certificat SSL
                ->post($apiUrl, $testData);
            
            $statusCode = $response->status();
            $responseBody = $response->json() ?: $response->body();
            $success = $statusCode >= 200 && $statusCode < 300;
            
            $statusClass = $success ? 'success' : 'danger';
            $statusText = $success ? 'Succès' : 'Échec';
            
            $html = "<div class='alert alert-{$statusClass}'>"
                . "<strong>Statut : {$statusText}</strong><br>"
                . "Code HTTP : {$statusCode}"
                . "</div>";
                
            if ($responseBody) {
                $responseJson = is_array($responseBody) ? json_encode($responseBody, JSON_PRETTY_PRINT) : $responseBody;
                $html .= "<div class='mt-3'>"
                    . "<h6>Réponse</h6>"
                    . "<pre class='bg-light p-2 rounded' style='max-height: 200px; overflow-y: auto;'>{$responseJson}</pre>"
                    . "</div>";
            }
            
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Test de connexion API');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors du test de connexion API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $html = "<div class='alert alert-danger'>"
                . "<strong>Erreur</strong><br>"
                . "Erreur de connexion : {$e->getMessage()}"
                . "</div>";
                
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Test de connexion API');
        }
    }
    
    /**
     * Teste la connexion à la base de données
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function testDatabaseConnection()
    {
        try {
            // Vérifier la connexion à la base de données
            $testConnection = DB::connection()->getPdo();
            
            // Récupérer les informations sur la base de données
            $driver = DB::connection()->getDriverName();
            $database = DB::connection()->getDatabaseName();
            $version = DB::select('SELECT version() as version')[0]->version;
            
            $html = "<div class='alert alert-success'>"
                . "<strong>Statut : Succès</strong><br>"
                . "Connexion à la base de données établie avec succès."
                . "</div>";
            
            $html .= "<div class='mt-3'>"
                . "<h6>Informations</h6>"
                . "<table class='table table-sm table-bordered'>"
                . "<tbody>"
                . "<tr><th>Driver</th><td>{$driver}</td></tr>"
                . "<tr><th>Base de données</th><td>{$database}</td></tr>"
                . "<tr><th>Version</th><td>{$version}</td></tr>"
                . "</tbody></table>"
                . "</div>";
            
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Test de connexion à la base de données');
        } catch (\Exception $e) {
            Log::error('Erreur lors du test de connexion à la base de données', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $html = "<div class='alert alert-danger'>"
                . "<strong>Erreur</strong><br>"
                . "Erreur de connexion à la base de données : {$e->getMessage()}"
                . "</div>";
            
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Test de connexion à la base de données');
        }
    }
    
    /**
     * Vérifie les permissions des fichiers
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkPermissions()
    {
        try {
            // Récupérer les permissions des fichiers
            $permissions = $this->getFilePermissions();
            
            // Vérifier s'il y a des avertissements ou des erreurs
            $hasWarnings = false;
            $hasErrors = false;
            
            foreach ($permissions as $item) {
                if (!$item['exists'] && $item['should_be_writable']) {
                    $hasErrors = true;
                } elseif (!$item['writable'] && $item['should_be_writable']) {
                    $hasErrors = true;
                } elseif (!$item['exists'] || (!$item['writable'] && !$item['should_be_writable'])) {
                    $hasWarnings = true;
                }
            }
            
            // Déterminer le statut et le message
            $statusClass = $hasErrors ? 'danger' : ($hasWarnings ? 'warning' : 'success');
            $statusText = $hasErrors ? 'Erreur' : ($hasWarnings ? 'Attention' : 'OK');
            $message = $hasErrors 
                ? 'Des problèmes critiques ont été détectés avec les permissions des fichiers.'
                : ($hasWarnings 
                    ? 'Des avertissements ont été détectés avec les permissions des fichiers.'
                    : 'Toutes les permissions de fichiers sont correctes.');
            
            // Générer le HTML pour l'alerte
            $html = "<div class='alert alert-{$statusClass}'>"
                . "<strong>Statut : {$statusText}</strong><br>"
                . "{$message}"
                . "</div>";
            
            // Générer le HTML pour le tableau des permissions
            $html .= "<div class='mt-3'>"
                . "<h6>Permissions des fichiers</h6>"
                . "<table class='table table-sm table-bordered'>"
                . "<thead>"
                . "<tr>"
                . "<th>Fichier/Dossier</th>"
                . "<th>Existe</th>"
                . "<th>Permissions</th>"
                . "<th>Accès en écriture</th>"
                . "</tr>"
                . "</thead>"
                . "<tbody>";
            
            // Ajouter chaque permission au tableau
            foreach ($permissions as $item) {
                $existsClass = $item['exists'] ? 'success' : 'danger';
                $writableClass = $item['writable'] ? 'success' : ($item['should_be_writable'] ? 'danger' : 'warning');
                $existsText = $item['exists'] ? 'Oui' : 'Non';
                $writableText = $item['writable'] ? 'Oui' : 'Non';
                
                $html .= "<tr>"
                    . "<td>{$item['name']}</td>"
                    . "<td><span class='badge bg-{$existsClass}'>{$existsText}</span></td>"
                    . "<td>{$item['permissions']}</td>"
                    . "<td><span class='badge bg-{$writableClass}'>{$writableText}</span></td>"
                    . "</tr>";
            }
            
            $html .= "</tbody></table></div>";
            
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Vérification des permissions');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des permissions', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $html = "<div class='alert alert-danger'>"
                . "<strong>Erreur</strong><br>"
                . "Erreur lors de la vérification des permissions : {$e->getMessage()}"
                . "</div>";
            
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Vérification des permissions');
        }
    }
    
    /**
     * Récupère les dernières entrées de log
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getLatestLogs(Request $request)
    {
        try {
            $logEntries = $this->getLatestLogEntries(20);
            
            if (count($logEntries) > 0) {
                $html = '';
                
                foreach ($logEntries as $entry) {
                    $html .= "<div class='log-entry mb-2'>"
                        . "<span class='text-muted small'>[{$entry['timestamp']}]</span>"
                        . "<pre class='mb-0 mt-1' style='white-space: pre-wrap; font-size: 0.8rem;'>{$entry['content']}</pre>"
                        . "</div>";
                }
            } else {
                $html = "<p class='text-muted'>Aucune entrée de log disponible</p>";
            }
            
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Derniers logs');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des logs', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $html = "<div class='alert alert-danger'>"
                . "<strong>Erreur</strong><br>"
                . "Erreur lors de la récupération des logs : {$e->getMessage()}"
                . "</div>";
            
            return redirect()->back()
                ->with('test_result', $html)
                ->with('test_result_title', 'Derniers logs');
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
                'api_keys' => DB::table('api_keys')->count(),
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
        $entries = [];
        $logsDir = storage_path('logs');
        
        // Essayer plusieurs formats de noms de fichiers de log possibles
        $possibleLogFiles = [
            $logsDir . '/laravel-' . date('Y-m-d') . '.log',  // Format standard par jour
            $logsDir . '/laravel.log',                       // Format simple
            $logsDir . '/laravel-debug.log',                 // Format debug
        ];
        
        // Trouver le fichier de log le plus récent
        $logFile = null;
        foreach ($possibleLogFiles as $file) {
            if (File::exists($file)) {
                $logFile = $file;
                break;
            }
        }
        
        // Si aucun fichier de log n'est trouvé, essayer de trouver d'autres fichiers .log
        if (!$logFile && File::exists($logsDir)) {
            $logFiles = File::glob($logsDir . '/*.log');
            if (!empty($logFiles)) {
                // Trier par date de modification (le plus récent d'abord)
                usort($logFiles, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $logFile = $logFiles[0];
            }
        }
        
        if ($logFile && File::exists($logFile)) {
            try {
                // Utiliser la commande tail pour lire les dernières lignes
                $process = new Process(['tail', '-n', $lines, $logFile]);
                $process->run();
                
                if ($process->isSuccessful()) {
                    $output = $process->getOutput();
                    
                    // Si le fichier est vide, essayer de lire le contenu complet (pour les petits fichiers)
                    if (empty($output) && filesize($logFile) < 1024 * 1024) { // Seulement si moins de 1Mo
                        $output = File::get($logFile);
                    }
                    
                    if (!empty($output)) {
                        $logEntries = preg_split('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $output, -1, PREG_SPLIT_DELIM_CAPTURE);
                        
                        // Ignorer le premier élément vide si présent
                        if (empty($logEntries[0])) {
                            array_shift($logEntries);
                        }
                        
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
                    
                    // Si aucune entrée n'est trouvée avec le format standard, créer une entrée
                    if (empty($entries) && !empty($output)) {
                        $entries[] = [
                            'timestamp' => date('Y-m-d H:i:s'),
                            'content' => $output
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de la récupération des logs', [
                    'message' => $e->getMessage()
                ]);
                
                // Ajouter cette erreur aux logs pour déboguer
                $entries[] = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'content' => 'Erreur lors de la récupération des logs: ' . $e->getMessage()
                ];
            }
        }
        
        // Si toujours pas d'entrées, créer une entrée pour indiquer le chemin du répertoire de logs
        if (empty($entries)) {
            $entries[] = [
                'timestamp' => date('Y-m-d H:i:s'),
                'content' => 'Aucun fichier de log trouvé dans ' . $logsDir . '. Désactivez et réactivez le mode debug dans config/app.php pour générer des logs.'
            ];
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
    
    /**
     * Récupère les détails des clés de série
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSerialKeys(Request $request)
    {
        try {
            $serialKeys = SerialKey::with('project')
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get()
                ->map(function($key) {
                    return [
                        'serial_key' => $key->serial_key,
                        'project_name' => $key->project ? $key->project->name : null,
                        'status' => ucfirst($key->status),
                        'status_class' => $this->getStatusClass($key->status),
                        'expires_at' => $key->expires_at ? Carbon::parse($key->expires_at)->format('d/m/Y') : null
                    ];
                });
            
            return response()->json([
                'success' => true,
                'items' => $serialKeys
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clés de série', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les détails des projets
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjects(Request $request)
    {
        try {
            $projects = Project::withCount('serialKeys')
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get()
                ->map(function($project) {
                    return [
                        'name' => $project->name,
                        'status' => $project->status ? 'Actif' : 'Inactif',
                        'status_class' => $project->status ? 'success' : 'danger',
                        'serial_keys_count' => $project->serial_keys_count,
                        'created_at' => Carbon::parse($project->created_at)->format('d/m/Y')
                    ];
                });
            
            return response()->json([
                'success' => true,
                'items' => $projects
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des projets', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les détails des administrateurs
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdmins(Request $request)
    {
        try {
            $admins = Admin::orderBy('name', 'asc')
                ->get()
                ->map(function($admin) {
                    return [
                        'name' => $admin->name,
                        'email' => $admin->email,
                        'last_login' => $admin->last_login_at ? Carbon::parse($admin->last_login_at)->format('d/m/Y H:i') : null
                    ];
                });
            
            return response()->json([
                'success' => true,
                'items' => $admins
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des administrateurs', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les détails des clés actives
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveKeys(Request $request)
    {
        try {
            $activeKeys = SerialKey::with('project')
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->take(30)
                ->get()
                ->map(function($key) {
                    return [
                        'serial_key' => $key->serial_key,
                        'project_name' => $key->project ? $key->project->name : null,
                        'domain' => $key->domain,
                        'expires_at' => $key->expires_at ? Carbon::parse($key->expires_at)->format('d/m/Y') : null
                    ];
                });
            
            return response()->json([
                'success' => true,
                'items' => $activeKeys
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clés actives', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère les détails des clés API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApiKeys(Request $request)
    {
        try {
            $apiKeys = DB::table('api_keys')
                ->leftJoin('projects', 'api_keys.project_id', '=', 'projects.id')
                ->select('api_keys.*', 'projects.name as project_name')
                ->orderBy('api_keys.created_at', 'desc')
                ->take(30)
                ->get()
                ->map(function($key) {
                    return [
                        'key' => $key->key,
                        'project_name' => $key->project_name,
                        'status' => $key->is_active ? 'Active' : 'Inactive',
                        'status_class' => $key->is_active ? 'success' : 'danger',
                        'last_used_at' => $key->last_used_at ? Carbon::parse($key->last_used_at)->format('d/m/Y H:i') : null
                    ];
                });
            
            return response()->json([
                'success' => true,
                'items' => $apiKeys
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clés API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtient la classe de couleur de badge en fonction du statut
     *
     * @param string $status
     * @return string
     */
    protected function getStatusClass($status)
    {
        switch (strtolower($status)) {
            case 'active':
                return 'success';
            case 'pending':
                return 'warning';
            case 'suspended':
                return 'info';
            case 'revoked':
            case 'expired':
                return 'danger';
            default:
                return 'secondary';
        }
    }
}
