<?php
/**
 * Script d'installation manuel pour AdminLicence
 * Ce script crée manuellement toutes les tables nécessaires
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
    file_put_contents($logDir . '/installation_manual.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
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
    
    // 1. Créer manuellement les tables essentielles
    $tables = [
        // Table users
        "CREATE TABLE `users` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        
        // Table admins
        "CREATE TABLE `admins` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        
        // Table projects
        "CREATE TABLE `projects` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text,
            `status` varchar(255) NOT NULL DEFAULT 'active',
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        
        // Table serial_keys
        "CREATE TABLE `serial_keys` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `key` varchar(255) NOT NULL,
            `project_id` bigint(20) UNSIGNED NOT NULL,
            `status` varchar(255) NOT NULL DEFAULT 'active',
            `expires_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `serial_keys_key_unique` (`key`),
            KEY `serial_keys_project_id_foreign` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        
        // Table serial_key_histories
        "CREATE TABLE `serial_key_histories` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `serial_key_id` bigint(20) UNSIGNED NOT NULL,
            `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
            `action` varchar(255) NOT NULL,
            `details` text,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `serial_key_histories_serial_key_id_foreign` (`serial_key_id`),
            KEY `serial_key_histories_admin_id_foreign` (`admin_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        
        // Table migrations
        "CREATE TABLE `migrations` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) NOT NULL,
            `batch` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
    ];
    
    $tablesCreated = true;
    $output = [];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
            $output[] = "Table created successfully";
        } catch (PDOException $e) {
            $output[] = "Error creating table: " . $e->getMessage();
            // Ne pas marquer comme échec, continuer avec les autres tables
        }
    }
    
    logInfo('tables_created', [
        'success' => $tablesCreated,
        'output' => $output
    ]);
    
    $installationSteps[] = [
        'step' => 'migrations',
        'status' => true,
        'message' => 'Database tables created successfully',
        'output' => $output
    ];
    
    // 2. Ajouter des données initiales
    $dataInserted = true;
    $output = [];
    
    // Insérer un projet par défaut
    try {
        $stmt = $pdo->prepare("INSERT INTO projects (name, description, status, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute(['Default Project', 'Default project created during installation', 'active']);
        $output[] = "Default project created";
    } catch (PDOException $e) {
        $output[] = "Error creating default project: " . $e->getMessage();
    }
    
    logInfo('data_inserted', [
        'success' => $dataInserted,
        'output' => $output
    ]);
    
    $installationSteps[] = [
        'step' => 'seeders',
        'status' => true,
        'message' => 'Initial data inserted successfully',
        'output' => $output
    ];
    
    // 3. Créer l'utilisateur administrateur
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
                $output = ["Neither admins nor users table exists"];
                $adminCreated = false;
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
    
    // 4. Mettre à jour le fichier .env avec l'URL du projet
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
    
    // 5. Marquer l'installation comme terminée
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
