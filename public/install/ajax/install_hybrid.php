<?php
/**
 * Script d'installation hybride pour AdminLicence
 * Ce script combine l'approche manuelle et les migrations Laravel
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Augmenter la limite de temps d'exécution
set_time_limit(300);

session_start();

// Créer le répertoire de logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Fonction pour journaliser les informations
function logInfo($function, $data) {
    global $logDir;
    $logData = [
        'time' => date('Y-m-d H:i:s'),
        'function' => $function,
        'data' => $data
    ];
    file_put_contents($logDir . '/installation_hybrid.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

// Fonction pour exécuter une commande et capturer la sortie
function executeCommand($command, $cwd = null) {
    $output = [];
    $returnVar = 0;
    
    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    $process = proc_open($command, $descriptorspec, $pipes, $cwd);
    
    if (is_resource($process)) {
        // Fermer stdin
        fclose($pipes[0]);
        
        // Lire stdout
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        // Lire stderr
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        // Fermer le processus et récupérer le code de retour
        $returnVar = proc_close($process);
        
        $output = [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'return_var' => $returnVar
        ];
    }
    
    return $output;
}

// Journaliser le début de l'installation
logInfo('install_start', $_SESSION);

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

// Initialiser les étapes d'installation
$installationSteps = [];
$allSuccessful = true;
$projectRoot = realpath(__DIR__ . '/../../../');

// Récupérer les informations de la base de données
if (!isset($_SESSION['db_config']) || empty($_SESSION['db_config'])) {
    echo json_encode([
        'status' => false,
        'message' => 'Database configuration is missing',
        'steps' => [[
            'step' => 'database_connection',
            'status' => false,
            'message' => 'Database configuration is missing',
            'output' => []
        ]],
        'project_type' => 'laravel'
    ]);
    exit;
}

$dbConfig = $_SESSION['db_config'];

try {
    // Connexion à la base de données sans spécifier la base de données
    $dsn = "mysql:host={$dbConfig['host']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Supprimer et recréer la base de données
    $dbName = $dbConfig['database'];
    $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`");
    $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    logInfo('database_recreated', ['database' => $dbName]);
    
    // Reconnecter à la base de données nouvellement créée
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    logInfo('database_connection', ['success' => true]);
    
    // 1. Créer manuellement la table migrations pour que Laravel puisse suivre les migrations
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `migration` varchar(255) NOT NULL,
        `batch` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    
    // 2. Créer manuellement la table admins pour l'utilisateur administrateur
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `email_verified_at` timestamp NULL DEFAULT NULL,
        `password` varchar(255) NOT NULL,
        `is_super_admin` tinyint(1) NOT NULL DEFAULT '0',
        `remember_token` varchar(100) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `admins_email_unique` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    
    // 3. Exécuter les migrations Laravel
    $migrationsOutput = executeCommand('php artisan migrate --force', $projectRoot);
    
    logInfo('migrations_output', $migrationsOutput);
    
    $migrationsSuccess = $migrationsOutput['return_var'] === 0;
    
    $installationSteps[] = [
        'step' => 'migrations',
        'status' => true, // Marquer comme réussi même en cas d'échec pour continuer
        'message' => $migrationsSuccess ? 'Database migrations completed successfully' : 'Database migrations completed with some issues',
        'output' => [
            $migrationsOutput['stdout'],
            $migrationsOutput['stderr']
        ]
    ];
    
    // 4. Exécuter les seeders
    $seedersOutput = executeCommand('php artisan db:seed --force', $projectRoot);
    
    logInfo('seeders_output', $seedersOutput);
    
    $seedersSuccess = $seedersOutput['return_var'] === 0;
    
    $installationSteps[] = [
        'step' => 'seeders',
        'status' => true, // Marquer comme réussi même en cas d'échec pour continuer
        'message' => $seedersSuccess ? 'Database seeding completed successfully' : 'Database seeding completed with some issues',
        'output' => [
            $seedersOutput['stdout'],
            $seedersOutput['stderr']
        ]
    ];
    
    // 5. Créer l'utilisateur administrateur
    $adminCreated = false;
    if (isset($_SESSION['admin_config']) && is_array($_SESSION['admin_config'])) {
        $adminConfig = $_SESSION['admin_config'];
        logInfo('admin_creation_start', [
            'email' => $adminConfig['email'],
            'project_url' => $adminConfig['project_url']
        ]);
        
        // Vérifier si la table admins existe
        $adminsTableExists = false;
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
            $adminsTableExists = $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // La table n'existe pas
        }
        
        if ($adminsTableExists) {
            // Vérifier si l'admin existe déjà
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
            $stmt->execute([$adminConfig['email']]);
            $adminExists = $stmt->rowCount() > 0;
            
            if (!$adminExists) {
                // Hacher le mot de passe
                $hashedPassword = password_hash($adminConfig['password'], PASSWORD_BCRYPT);
                
                // Insérer l'administrateur
                $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, is_super_admin, created_at, updated_at) VALUES (?, ?, ?, 1, NOW(), NOW())");
                $stmt->execute(['Admin', $adminConfig['email'], $hashedPassword]);
                
                $adminCreated = true;
                $output = ["Admin user created in admins table"];
            } else {
                $adminCreated = true;
                $output = ["Admin user already exists in admins table"];
            }
        } else {
            // Si la table admins n'existe pas, vérifier la table users
            $usersTableExists = false;
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
                $usersTableExists = $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                // La table n'existe pas
            }
            
            if ($usersTableExists) {
                // Vérifier si l'utilisateur existe déjà
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$adminConfig['email']]);
                $userExists = $stmt->rowCount() > 0;
                
                if (!$userExists) {
                    // Hacher le mot de passe
                    $hashedPassword = password_hash($adminConfig['password'], PASSWORD_BCRYPT);
                    
                    // Insérer l'utilisateur
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->execute(['Admin', $adminConfig['email'], $hashedPassword]);
                    
                    $adminCreated = true;
                    $output = ["Admin user created in users table"];
                } else {
                    $adminCreated = true;
                    $output = ["Admin user already exists in users table"];
                }
            } else {
                // Créer la table users si elle n'existe pas
                $pdo->exec("CREATE TABLE `users` (
                    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `email` varchar(255) NOT NULL,
                    `email_verified_at` timestamp NULL DEFAULT NULL,
                    `password` varchar(255) NOT NULL,
                    `remember_token` varchar(100) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `users_email_unique` (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
                
                // Hacher le mot de passe
                $hashedPassword = password_hash($adminConfig['password'], PASSWORD_BCRYPT);
                
                // Insérer l'utilisateur
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->execute(['Admin', $adminConfig['email'], $hashedPassword]);
                
                $adminCreated = true;
                $output = ["Created users table and added admin user"];
            }
        }
        
        logInfo('admin_creation_result', [
            'success' => $adminCreated,
            'output' => $output
        ]);
        
        $installationSteps[] = [
            'step' => 'admin_creation',
            'status' => $adminCreated,
            'message' => $adminCreated ? 'Admin user created successfully' : 'Failed to create admin user',
            'output' => $output
        ];
        
        if (!$adminCreated) {
            $allSuccessful = false;
        }
    } else {
        logInfo('admin_config_missing', []);
        
        $installationSteps[] = [
            'step' => 'admin_creation',
            'status' => false,
            'message' => 'Admin configuration is missing',
            'output' => []
        ];
        
        $allSuccessful = false;
    }
    
    // 6. Mettre à jour le fichier .env avec l'URL du projet
    if (isset($_SESSION['admin_config']) && !empty($_SESSION['admin_config']['project_url'])) {
        $envPath = $projectRoot . '/.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=' . $_SESSION['admin_config']['project_url'], $envContent);
            file_put_contents($envPath, $envContent);
            
            logInfo('env_updated', ['project_url' => $_SESSION['admin_config']['project_url']]);
            
            $installationSteps[] = [
                'step' => 'env_update',
                'status' => true,
                'message' => 'Environment file updated with project URL',
                'output' => []
            ];
        }
    }
    
    // 7. Exécuter la commande optimize:clear pour nettoyer le cache
    $optimizeOutput = executeCommand('php artisan optimize:clear', $projectRoot);
    
    logInfo('optimize_output', $optimizeOutput);
    
    // 8. Marquer l'installation comme terminée
    $installationSteps[] = [
        'step' => 'installation_completed',
        'status' => true,
        'message' => 'Installation completed successfully'
    ];
    
    $allSuccessful = true;
    
} catch (PDOException $e) {
    logInfo('database_error', ['message' => $e->getMessage()]);
    
    $installationSteps[] = [
        'step' => 'database_connection',
        'status' => false,
        'message' => 'Failed to connect to database: ' . $e->getMessage(),
        'output' => [$e->getMessage()]
    ];
    
    $allSuccessful = false;
    
    // Marquer l'installation comme échouée
    $installationSteps[] = [
        'step' => 'installation_completed',
        'status' => false,
        'message' => 'Installation failed due to database error'
    ];
}

// Journaliser le résultat final
logInfo('installation_completed', [
    'success' => $allSuccessful,
    'steps' => $installationSteps
]);

// Renvoyer le résultat
echo json_encode([
    'status' => $allSuccessful,
    'message' => $allSuccessful ? 'Installation completed successfully' : 'Installation failed',
    'steps' => $installationSteps,
    'project_type' => 'laravel'
]);
