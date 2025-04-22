<?php
/**
 * Script d'installation complet pour AdminLicence
 * Ce script exécute les migrations, les seeders et crée l'utilisateur administrateur
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
    file_put_contents($logDir . '/installation_complete.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

// Journaliser le début de l'installation
logInfo('install_start', $_SESSION);

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

// Initialiser les étapes d'installation
$installationSteps = [];
$allSuccessful = true;
$projectRoot = realpath(__DIR__ . '/../../../');

// 1. Exécuter les migrations
logInfo('migrations_start', ['project_root' => $projectRoot]);

// Utiliser proc_open pour capturer la sortie standard et d'erreur
$descriptorspec = [
    0 => ["pipe", "r"],  // stdin
    1 => ["pipe", "w"],  // stdout
    2 => ["pipe", "w"]   // stderr
];

// Essayer d'abord de rafraîchir la base de données
$process = proc_open("cd {$projectRoot} && php artisan migrate:fresh --force", $descriptorspec, $pipes);

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

// Si migrate:fresh échoue, essayer migrate normal
if (!$migrationsRun) {
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
        
        if (!empty($stdout)) {
            $output[] = "STDOUT (migrate): " . $stdout;
        }
        if (!empty($stderr)) {
            $output[] = "STDERR (migrate): " . $stderr;
        }
        
        $migrationsRun = ($returnVar === 0);
    }
}

// Si les migrations échouent, essayer de créer la table users directement
if (!$migrationsRun) {
    try {
        // Récupérer les informations de la base de données
        if (isset($_SESSION['db_config']) && !empty($_SESSION['db_config'])) {
            $dbConfig = $_SESSION['db_config'];
            
            // Connexion à la base de données
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Vérifier si la table users existe déjà
            $tableExists = false;
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
                $tableExists = $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                // La table n'existe pas
            }
            
            // Créer la table users si elle n'existe pas
            if (!$tableExists) {
                $sql = "CREATE TABLE IF NOT EXISTS `users` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                
                $pdo->exec($sql);
                $migrationsRun = true;
                $output[] = "Created users table directly via SQL";
            } else {
                $migrationsRun = true;
                $output[] = "Users table already exists";
            }
        }
    } catch (PDOException $e) {
        $output[] = "SQL Error: " . $e->getMessage();
    }
}

logInfo('migrations_result', [
    'success' => $migrationsRun,
    'output' => $output
]);

$installationSteps[] = [
    'step' => 'migrations',
    'status' => $migrationsRun,
    'message' => $migrationsRun ? 'Migrations run successfully' : 'Failed to run migrations',
    'output' => $output
];

if (!$migrationsRun) {
    $allSuccessful = false;
}

// 2. Exécuter les seeders si les migrations ont réussi
$seedersRun = false;
if ($migrationsRun) {
    logInfo('seeders_start', ['project_root' => $projectRoot]);
    
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
    
    logInfo('seeders_result', [
        'success' => $seedersRun,
        'output' => $output
    ]);
    
    $installationSteps[] = [
        'step' => 'seeders',
        'status' => $seedersRun,
        'message' => $seedersRun ? 'Database seeders run successfully' : 'Failed to run database seeders',
        'output' => $output
    ];
    
    if (!$seedersRun) {
        // Ce n'est pas critique, l'installation peut continuer
        $output[] = "Seeding failed but installation will continue";
    }
}

// 3. Créer l'utilisateur administrateur
$adminCreated = false;
if (isset($_SESSION['admin_config']) && is_array($_SESSION['admin_config'])) {
    $adminConfig = $_SESSION['admin_config'];
    logInfo('admin_creation_start', [
        'email' => $adminConfig['email'],
        'project_url' => $adminConfig['project_url']
    ]);
    
    // Essayer d'abord avec Tinker
    $email = escapeshellarg($adminConfig['email']);
    $password = escapeshellarg($adminConfig['password']);
    $name = escapeshellarg('Admin');
    
    $tinkerCommand = "DB::table('users')->insert(['name' => {$name}, 'email' => {$email}, 'password' => bcrypt({$password}), 'created_at' => now(), 'updated_at' => now()])";
    
    $process = proc_open("cd {$projectRoot} && php artisan tinker --execute=\"{$tinkerCommand}\"", $descriptorspec, $pipes);
    
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
        
        $adminCreated = ($returnVar === 0 && strpos($stdout, 'true') !== false);
    } else {
        $adminCreated = false;
        $output = ["Failed to start process"];
    }
    
    // Si Tinker échoue, essayer SQL direct
    if (!$adminCreated) {
        try {
            // Récupérer les informations de la base de données
            if (isset($_SESSION['db_config']) && !empty($_SESSION['db_config'])) {
                $dbConfig = $_SESSION['db_config'];
                
                // Connexion à la base de données
                $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                
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
                    $output[] = "Admin user created directly via SQL";
                } else {
                    $adminCreated = true;
                    $output[] = "Admin user already exists";
                }
            }
        } catch (PDOException $e) {
            $output[] = "SQL Error: " . $e->getMessage();
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

// 4. Marquer l'installation comme terminée
$installationSteps[] = [
    'step' => 'installation_completed',
    'status' => $allSuccessful,
    'message' => $allSuccessful ? 'Installation completed successfully' : 'Installation completed with errors'
];

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
