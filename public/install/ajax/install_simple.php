<?php
/**
 * Script d'installation simplifié pour AdminLicence
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
    file_put_contents($logDir . '/installation_simple.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

// Journaliser le début de l'installation
logInfo('install_start', $_SESSION);

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

// Récupérer les informations nécessaires
$projectRoot = realpath(__DIR__ . '/../../../');
$installationSteps = [];

// 1. Exécuter les migrations
logInfo('migrations_start', ['project_root' => $projectRoot]);

// Utiliser proc_open pour capturer la sortie standard et d'erreur
$descriptorspec = [
    0 => ["pipe", "r"],  // stdin
    1 => ["pipe", "w"],  // stdout
    2 => ["pipe", "w"]   // stderr
];

// Utiliser l'option --force et --step pour ignorer les migrations déjà exécutées
$process = proc_open("cd {$projectRoot} && php artisan migrate --force --step", $descriptorspec, $pipes);

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

logInfo('migrations_result', [
    'success' => $migrationsRun,
    'output' => $output,
    'return_code' => $returnVar ?? null
]);

$installationSteps[] = [
    'step' => 'migrations',
    'status' => $migrationsRun,
    'message' => $migrationsRun ? 'Migrations run successfully' : 'Failed to run migrations',
    'output' => $output
];

// 2. Exécuter les seeders si les migrations ont réussi
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
        'output' => $output,
        'return_code' => $returnVar ?? null
    ]);
    
    $installationSteps[] = [
        'step' => 'seeders',
        'status' => $seedersRun,
        'message' => $seedersRun ? 'Database seeders run successfully' : 'Failed to run database seeders',
        'output' => $output
    ];
}

// 3. Créer l'utilisateur administrateur
if (isset($_SESSION['admin_config']) && is_array($_SESSION['admin_config'])) {
    logInfo('admin_creation_start', [
        'email' => $_SESSION['admin_config']['email'],
        'project_url' => $_SESSION['admin_config']['project_url']
    ]);
    
    $email = escapeshellarg($_SESSION['admin_config']['email']);
    $password = escapeshellarg($_SESSION['admin_config']['password']);
    $name = escapeshellarg('Admin');
    
    // Créer l'utilisateur avec Tinker - Utiliser une approche différente pour éviter les problèmes d'échappement
    $command = "DB::table('users')->insert(['name' => {$name}, 'email' => {$email}, 'password' => bcrypt({$password}), 'created_at' => now(), 'updated_at' => now()])";
    
    $process = proc_open("cd {$projectRoot} && php artisan tinker --execute=\"{$command}\"", $descriptorspec, $pipes);
    
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
        
        $adminCreated = ($returnVar === 0);
    } else {
        $adminCreated = false;
        $output = ["Failed to start process"];
    }
    
    logInfo('admin_creation_result', [
        'success' => $adminCreated,
        'output' => $output,
        'return_code' => $returnVar ?? null
    ]);
    
    $installationSteps[] = [
        'step' => 'admin_creation',
        'status' => $adminCreated,
        'message' => $adminCreated ? 'Admin user created successfully' : 'Failed to create admin user',
        'output' => $output
    ];
}

// 4. Marquer l'installation comme terminée
$installationCompleted = true;
$installationSteps[] = [
    'step' => 'installation_completed',
    'status' => $installationCompleted,
    'message' => 'Installation marked as complete'
];

// Vérifier si toutes les étapes ont réussi
$allSuccessful = true;
foreach ($installationSteps as $step) {
    if (!$step['status']) {
        $allSuccessful = false;
        break;
    }
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
