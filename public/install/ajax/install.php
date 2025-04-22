<?php
/**
 * AJAX handler for final installation
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir un gestionnaire d'erreurs personnalisé pour capturer les erreurs fatales
function captureErrorsAsJson($errno, $errstr, $errfile, $errline) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Journaliser l'erreur
    $logData = [
        'time' => date('Y-m-d H:i:s'),
        'function' => 'error_handler',
        'error_number' => $errno,
        'error_message' => $errstr,
        'error_file' => $errfile,
        'error_line' => $errline
    ];
    file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    
    // Renvoyer une réponse JSON avec l'erreur
    header('Content-Type: application/json');
    echo json_encode([
        'status' => false,
        'message' => "Une erreur s'est produite: {$errstr} dans {$errfile} à la ligne {$errline}",
        'steps' => [[
            'step' => 'error',
            'status' => false,
            'message' => "Une erreur s'est produite: {$errstr}",
            'output' => ["Erreur dans {$errfile} à la ligne {$errline}"]
        ]],
        'project_type' => 'unknown'
    ]);
    
    return true; // Empêcher l'affichage de l'erreur standard
}

// Enregistrer le gestionnaire d'erreurs
set_error_handler('captureErrorsAsJson');

// Capturer les erreurs fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Journaliser l'erreur fatale
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'fatal_error_handler',
            'error' => $error
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        // Renvoyer une réponse JSON avec l'erreur
        header('Content-Type: application/json');
        echo json_encode([
            'status' => false,
            'message' => "Une erreur fatale s'est produite: {$error['message']} dans {$error['file']} à la ligne {$error['line']}",
            'steps' => [[
                'step' => 'fatal_error',
                'status' => false,
                'message' => "Une erreur fatale s'est produite: {$error['message']}",
                'output' => ["Erreur dans {$error['file']} à la ligne {$error['line']}"]
            ]],
            'project_type' => 'unknown'
        ]);
    }
});

session_start();

// Include utility functions
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Créer le répertoire de logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Journaliser le début de l'installation
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'install',
    'session' => $_SESSION
];
file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Check if all required steps have been completed
if (!isset($_SESSION['license_verified']) || !$_SESSION['license_verified']) {
    echo json_encode([
        'status' => false,
        'message' => 'License verification is required',
    ]);
    exit;
}

if (!isset($_SESSION['db_tested']) || !$_SESSION['db_tested']) {
    echo json_encode([
        'status' => false,
        'message' => 'Database configuration is required',
    ]);
    exit;
}

if (!isset($_SESSION['admin_saved']) || !$_SESSION['admin_saved']) {
    echo json_encode([
        'status' => false,
        'message' => 'Admin account configuration is required',
    ]);
    exit;
}

// Perform installation steps
$installationSteps = [];

// Récupérer le type de projet
$projectType = $_SESSION['project_type'] ?? 'php';
$projectRoot = realpath(__DIR__ . '/../../../');

// 1. Vérifier et créer le fichier .env pour les projets Laravel si nécessaire
if ($projectType === 'laravel' && (!isset($_SESSION['has_env']) || !$_SESSION['has_env'])) {
    $envCreated = false;
    
    // Créer le fichier .env avec les informations de base de données
    $dbConfig = $_SESSION['db_config'] ?? [];
    $envData = [
        'APP_NAME' => 'Laravel',
        'APP_ENV' => 'production',
        'APP_KEY' => generateRandomKey(),
        'APP_DEBUG' => 'false',
        'APP_URL' => $_SESSION['admin_config']['project_url'] ?? 'http://localhost',
        
        'LOG_CHANNEL' => 'stack',
        'LOG_DEPRECATIONS_CHANNEL' => 'null',
        'LOG_LEVEL' => 'debug',
    ];
    
    // Ajouter les informations de base de données si disponibles
    if (!empty($dbConfig)) {
        $envData['DB_CONNECTION'] = 'mysql';
        $envData['DB_HOST'] = $dbConfig['host'] ?? '127.0.0.1';
        $envData['DB_PORT'] = $dbConfig['port'] ?? '3306';
        $envData['DB_DATABASE'] = $dbConfig['database'] ?? 'laravel';
        $envData['DB_USERNAME'] = $dbConfig['username'] ?? 'root';
        $envData['DB_PASSWORD'] = $dbConfig['password'] ?? '';
    }
    
    $envCreated = updateEnvFile($envData);
    $_SESSION['has_env'] = $envCreated;
    
    $installationSteps[] = [
        'step' => 'env_creation',
        'status' => $envCreated,
        'message' => $envCreated ? 'Environment file created successfully' : 'Failed to create environment file',
    ];
    
    // Journaliser la création du fichier .env
    $logData = [
        'time' => date('Y-m-d H:i:s'),
        'function' => 'create_env_file',
        'status' => $envCreated ? 'success' : 'failed'
    ];
    file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

// 2. Vérifier et installer vendor pour les projets Laravel si nécessaire
if ($projectType === 'laravel' && (!isset($_SESSION['has_vendor']) || !$_SESSION['has_vendor'])) {
    $vendorInstalled = false;
    
    // Installer les dépendances vendor
    $command = "cd {$projectRoot} && composer install --no-interaction";
    
    // Journaliser la commande
    $logData = [
        'time' => date('Y-m-d H:i:s'),
        'function' => 'install_vendor',
        'command' => $command
    ];
    file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    
    // Exécuter la commande
    exec($command, $output, $returnVar);
    
    // Vérifier si l'installation a réussi
    $vendorInstalled = ($returnVar === 0 && is_dir($projectRoot . '/vendor'));
    $_SESSION['has_vendor'] = $vendorInstalled;
    
    $installationSteps[] = [
        'step' => 'vendor_installation',
        'status' => $vendorInstalled,
        'message' => $vendorInstalled ? 'Vendor dependencies installed successfully' : 'Failed to install vendor dependencies',
        'output' => $output
    ];
    
    // Journaliser le résultat
    $logData = [
        'time' => date('Y-m-d H:i:s'),
        'function' => 'install_vendor',
        'status' => $vendorInstalled ? 'success' : 'failed',
        'output' => $output
    ];
    file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

// 3. Create admin user
$adminCreated = false;
if (isset($_SESSION['admin_config']) && is_array($_SESSION['admin_config'])) {
    // Journaliser les informations de l'administrateur
    $logData = [
        'time' => date('Y-m-d H:i:s'),
        'function' => 'admin_creation_start',
        'admin_config' => [
            'email' => $_SESSION['admin_config']['email'],
            'project_url' => $_SESSION['admin_config']['project_url']
        ]
    ];
    file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    
    // Pour Laravel 12, nous devons utiliser une approche différente pour créer l'utilisateur admin
    // car la structure a changé
    if ($projectType === 'laravel') {
        // Créer une commande artisan personnalisée pour créer l'utilisateur
        $email = escapeshellarg($_SESSION['admin_config']['email']);
        $password = escapeshellarg($_SESSION['admin_config']['password']);
        $name = escapeshellarg('Admin');
        
        // Essayer d'abord avec la commande tinker standard
        $command = "cd {$projectRoot} && php artisan tinker --execute=\"\\App\\Models\\User::create(['name' => {$name}, 'email' => {$email}, 'password' => bcrypt({$password})])\"";
        
        // Journaliser la commande
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'create_admin_user',
            'command' => $command
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        // Exécuter la commande
        exec($command, $output, $returnVar);
        
        // Vérifier si la création a réussi
        $adminCreated = ($returnVar === 0);
        
        // Journaliser le résultat
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'create_admin_user_result',
            'status' => $adminCreated ? 'success' : 'failed',
            'output' => $output
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        // Si la première méthode échoue, essayer une autre approche
        if (!$adminCreated) {
            // Essayer avec une commande SQL directe
            $command = "cd {$projectRoot} && php artisan db:seed --class=AdminUserSeeder";
            
            // Journaliser la commande
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'function' => 'create_admin_user_alternative',
                'command' => $command
            ];
            file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
            
            // Exécuter la commande
            exec($command, $output, $returnVar);
            
            // Vérifier si la création a réussi
            $adminCreated = ($returnVar === 0);
            
            // Journaliser le résultat
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'function' => 'create_admin_user_alternative_result',
                'status' => $adminCreated ? 'success' : 'failed',
                'output' => $output
            ];
            file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        }
    } else {
        // Pour les projets non-Laravel, utiliser la fonction standard
        $adminCreated = createAdminUser($_SESSION['admin_config']);
    }
}

$installationSteps[] = [
    'step' => 'admin_creation',
    'status' => $adminCreated,
    'message' => $adminCreated ? 'Admin user created successfully' : 'Failed to create admin user',
];

// 4. For Laravel projects, run migrations if possible
$migrationsRun = false;
if ($projectType === 'laravel' && isset($_SESSION['has_vendor']) && $_SESSION['has_vendor']) {
    // Vérifier que la base de données est configurée correctement
    if (isset($_SESSION['db_config']) && !empty($_SESSION['db_config']['database'])) {
        // Journaliser les informations de la base de données
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'database_info',
            'db_config' => $_SESSION['db_config']
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        // Créer la base de données si elle n'existe pas en utilisant une connexion PDO directe
        $dbConfig = $_SESSION['db_config'];
        
        // Journaliser l'intention de créer la base de données
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'create_database_start',
            'database' => $dbConfig['database']
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        try {
            // Connexion sans spécifier la base de données
            $dsn = "mysql:host={$dbConfig['host']};port=3306;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Essayer de créer la base de données si elle n'existe pas
            $dbName = $dbConfig['database'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            $createDbSuccess = true;
            $createDbOutput = ['Database created or already exists'];
            
            // Journaliser le succès
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'function' => 'create_database_result',
                'status' => 'success',
                'message' => "Database {$dbName} created or already exists"
            ];
            file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        } catch (PDOException $e) {
            $createDbSuccess = false;
            $createDbOutput = [$e->getMessage()];
            
            // Journaliser l'erreur
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'function' => 'create_database_result',
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
            file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        }
        
        // Vérifier si la base de données a été créée avec succès
        if (isset($createDbSuccess) && $createDbSuccess) {
            // Exécuter les migrations
            $command = "cd {$projectRoot} && php artisan migrate --force 2>&1";
            
            // Journaliser la commande
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'function' => 'run_migrations',
                'command' => $command
            ];
            file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
            
            // Exécuter la commande avec une durée d'exécution plus longue
            set_time_limit(300); // Augmenter la limite de temps à 5 minutes
            
            // Utiliser proc_open pour capturer la sortie standard et d'erreur
            $descriptorspec = [
                0 => ["pipe", "r"],  // stdin
                1 => ["pipe", "w"],  // stdout
                2 => ["pipe", "w"]   // stderr
            ];
            
            $process = proc_open("cd {$projectRoot} && php artisan migrate --force", $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                // Fermer stdin
                fclose($pipes[0]);
                
                // Lire stdout
                $stdout = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                
                // Lire stderr
                $stderr = stream_get_contents($pipes[2]);
                fclose($pipes[2]);
                
                // Fermer le processus et obtenir le code de retour
                $returnVar = proc_close($process);
                
                $output = [];
                if (!empty($stdout)) {
                    $output[] = "STDOUT: " . $stdout;
                }
                if (!empty($stderr)) {
                    $output[] = "STDERR: " . $stderr;
                }
                
                $migrationsRun = ($returnVar === 0);
            } else {
                $migrationsRun = false;
                $output = ["Failed to start process"];
            }
            
            $installationSteps[] = [
                'step' => 'migrations',
                'status' => $migrationsRun,
                'message' => $migrationsRun ? 'Migrations run successfully' : 'Failed to run migrations',
                'output' => $output
            ];
            
            // Journaliser le résultat
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'function' => 'run_migrations',
                'status' => $migrationsRun ? 'success' : 'failed',
                'output' => $output,
                'return_code' => $returnVar ?? null
            ];
            file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        } else {
            $migrationsRun = false;
            $installationSteps[] = [
                'step' => 'migrations',
                'status' => false,
                'message' => 'Failed to create database',
                'output' => $createDbOutput ?? ['Unknown error']
            ];
            
            // Journaliser l'erreur
            $logData = [
                'time' => date('Y-m-d H:i:s'),
                'function' => 'run_migrations',
                'status' => 'failed',
                'message' => 'Failed to create database',
                'output' => $createDbOutput ?? ['Unknown error']
            ];
            file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        }
    } else {
        // Journaliser l'erreur
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'run_migrations',
            'status' => 'error',
            'message' => 'Database configuration is missing or incomplete'
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        $installationSteps[] = [
            'step' => 'migrations',
            'status' => false,
            'message' => 'Database configuration is missing or incomplete'
        ];
    }
    
    // Exécuter les seeders si les migrations ont réussi
    if ($migrationsRun) {
        $command = "cd {$projectRoot} && php artisan db:seed --force 2>&1";
        
        // Journaliser la commande
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'run_seeders',
            'command' => $command
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        // Utiliser proc_open pour capturer la sortie standard et d'erreur
        $descriptorspec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];
        
        $process = proc_open("cd {$projectRoot} && php artisan db:seed --force", $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            // Fermer stdin
            fclose($pipes[0]);
            
            // Lire stdout
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            
            // Lire stderr
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            
            // Fermer le processus et obtenir le code de retour
            $returnVar = proc_close($process);
            
            $output = [];
            if (!empty($stdout)) {
                $output[] = "STDOUT: " . $stdout;
            }
            if (!empty($stderr)) {
                $output[] = "STDERR: " . $stderr;
            }
            
            $seedersRun = ($returnVar === 0);
        } else {
            $seedersRun = false;
            $output = ["Failed to start process"];
        }
        
        $installationSteps[] = [
            'step' => 'seeders',
            'status' => $seedersRun,
            'message' => $seedersRun ? 'Database seeders run successfully' : 'Failed to run database seeders',
            'output' => $output
        ];
        
        // Journaliser le résultat
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'run_seeders',
            'status' => $seedersRun ? 'success' : 'failed',
            'output' => $output,
            'return_code' => $returnVar ?? null
        ];
        file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    }
}

// 5. Mark installation as complete
$installationCompleted = completeInstallation();
$installationSteps[] = [
    'step' => 'installation_completed',
    'status' => $installationCompleted,
    'message' => $installationCompleted ? 'Installation marked as complete' : 'Failed to mark installation as complete',
];

// Check if all steps were successful
$allSuccessful = true;
foreach ($installationSteps as $step) {
    if (!$step['status']) {
        $allSuccessful = false;
        break;
    }
}

// Journaliser le résultat final
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'installation_completed',
    'status' => $allSuccessful ? 'success' : 'failed',
    'steps' => $installationSteps
];
file_put_contents($logDir . '/installation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Return result
echo json_encode([
    'status' => $allSuccessful,
    'message' => $allSuccessful ? 'Installation completed successfully' : 'Installation failed',
    'steps' => $installationSteps,
    'project_type' => $projectType
]);
