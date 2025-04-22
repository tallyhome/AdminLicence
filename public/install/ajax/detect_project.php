<?php
/**
 * AJAX handler for project detection
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir les en-têtes CORS pour permettre les requêtes AJAX
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Démarrer la session
session_start();

// Inclure les fonctions utilitaires
require_once '../includes/functions.php';

// Créer le répertoire de logs
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Journaliser la requête pour débogage
$requestData = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'detect_project_request',
    'method' => $_SERVER['REQUEST_METHOD'],
    'query' => $_GET,
    'server' => $_SERVER
];
file_put_contents($logDir . '/detect_project_debug.log', json_encode($requestData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Détecter le type de projet
$projectInfo = detectProjectType();

// Vérification forcée du fichier .env et du répertoire vendor
$projectRoot = $projectInfo['project_root'];

// Créer un fichier de log détaillé pour le débogage
$debugData = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'detect_project_detailed',
    'initial_project_root' => $projectRoot,
    'paths_checked' => []
];

// Vérifier si nous sommes dans le répertoire public d'un projet Laravel
if (basename($projectRoot) === 'public') {
    // Remonter d'un niveau pour obtenir la racine du projet
    $parentRoot = realpath($projectRoot . '/..');
    $debugData['paths_checked']['parent_root'] = $parentRoot;
    
    // Vérifier si le répertoire parent est un projet Laravel
    $isLaravelParent = file_exists($parentRoot . '/artisan') && 
                       is_dir($parentRoot . '/routes') && 
                       file_exists($parentRoot . '/routes/web.php');
    
    $debugData['paths_checked']['artisan_parent'] = [
        'path' => $parentRoot . '/artisan',
        'exists' => file_exists($parentRoot . '/artisan')
    ];
    
    $debugData['paths_checked']['routes_dir_parent'] = [
        'path' => $parentRoot . '/routes',
        'exists' => is_dir($parentRoot . '/routes')
    ];
    
    $debugData['paths_checked']['web_php_parent'] = [
        'path' => $parentRoot . '/routes/web.php',
        'exists' => file_exists($parentRoot . '/routes/web.php')
    ];
    
    $debugData['is_laravel_parent'] = $isLaravelParent;
    
    if ($isLaravelParent) {
        // Si le parent est un projet Laravel, utiliser ce chemin comme racine du projet
        $projectRoot = $parentRoot;
        $projectInfo['project_root'] = $projectRoot;
        $projectInfo['type'] = 'laravel';
        $debugData['adjusted_project_root'] = $projectRoot;
    }
}

// Vérification forcée du fichier .env
$envExists = file_exists($projectRoot . '/.env');
$projectInfo['has_env'] = $envExists;

$debugData['paths_checked']['env_file'] = [
    'path' => $projectRoot . '/.env',
    'exists' => $envExists
];

// Vérification forcée du répertoire vendor
$vendorExists = is_dir($projectRoot . '/vendor');
$projectInfo['has_vendor'] = $vendorExists;

$debugData['paths_checked']['vendor_dir'] = [
    'path' => $projectRoot . '/vendor',
    'exists' => $vendorExists
];

// Journaliser les informations détaillées
file_put_contents($logDir . '/detect_project_detailed.log', json_encode($debugData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Journaliser la détection après vérification forcée
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'detect_project',
    'project_info' => $projectInfo,
    'env_exists' => $projectInfo['has_env'] ? 'oui' : 'non',
    'vendor_exists' => $projectInfo['has_vendor'] ? 'oui' : 'non'
];
file_put_contents($logDir . '/project_detection.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Stocker les informations dans la session
$_SESSION['project_type'] = $projectInfo['type'];
$_SESSION['has_env'] = $projectInfo['has_env'];
$_SESSION['has_vendor'] = $projectInfo['has_vendor'];

// Pour les projets Laravel, créer le fichier .env s'il n'existe pas
$envCreated = false;
$vendorInstalled = false;

if ($projectInfo['type'] === 'laravel') {
    // Créer le fichier .env s'il n'existe pas
    if (!$projectInfo['has_env']) {
        // Vérifier si .env.example existe
        $envExamplePath = $projectRoot . '/.env.example';
        $envPath = $projectRoot . '/.env';
        
        // Journaliser la vérification de .env.example
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'create_env_file',
            'env_example_path' => $envExamplePath,
            'env_example_exists' => file_exists($envExamplePath)
        ];
        file_put_contents($logDir . '/env_creation.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        if (file_exists($envExamplePath)) {
            // Copier .env.example vers .env
            $envContent = file_get_contents($envExamplePath);
            
            // Générer une nouvelle clé d'application si nécessaire
            $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . generateRandomKeyLocal(), $envContent);
            
            // Écrire le fichier .env
            $envCreated = file_put_contents($envPath, $envContent) !== false;
        } else {
            // Créer un fichier .env par défaut si .env.example n'existe pas
            $envData = [
                'APP_NAME' => 'AdminLicence',
                'APP_ENV' => 'local',
                'APP_KEY' => generateRandomKeyLocal(),
                'APP_DEBUG' => 'true',
                'APP_URL' => 'http://localhost',
                
                'LOG_CHANNEL' => 'stack',
                'LOG_DEPRECATIONS_CHANNEL' => 'null',
                'LOG_LEVEL' => 'debug',
                
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => '127.0.0.1',
                'DB_PORT' => '3306',
                'DB_DATABASE' => 'adminlicence',
                'DB_USERNAME' => 'root',
                'DB_PASSWORD' => '',
                
                'SESSION_DRIVER' => 'database',
                'SESSION_LIFETIME' => '120',
                'CACHE_STORE' => 'database',
                'QUEUE_CONNECTION' => 'database',
            ];
            
            $envCreated = updateEnvFileLocal($envData);
        }
        
        $projectInfo['has_env'] = $envCreated;
        $projectInfo['env_created'] = $envCreated;
        
        // Mettre à jour la session
        $_SESSION['has_env'] = $envCreated;
        $_SESSION['env_created'] = $envCreated;
        
        // Journaliser la création du fichier .env
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'create_env_file',
            'status' => $envCreated ? 'success' : 'failed'
        ];
        file_put_contents($logDir . '/project_detection.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    }
    
    // Installer les dépendances vendor si elles sont absentes
    if (!$projectInfo['has_vendor']) {
        $projectRoot = $projectInfo['project_root'];
        $command = "cd {$projectRoot} && composer install --no-interaction";
        
        // Journaliser la commande
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'install_vendor',
            'command' => $command
        ];
        file_put_contents($logDir . '/project_detection.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
        
        // Exécuter la commande
        exec($command, $output, $returnVar);
        
        // Vérifier si l'installation a réussi
        $vendorInstalled = ($returnVar === 0 && is_dir($projectRoot . '/vendor'));
        $projectInfo['has_vendor'] = $vendorInstalled;
        $projectInfo['vendor_installed'] = $vendorInstalled;
        
        // Mettre à jour la session
        $_SESSION['has_vendor'] = $vendorInstalled;
        $_SESSION['vendor_installed'] = $vendorInstalled;
        
        // Journaliser le résultat
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'function' => 'install_vendor',
            'status' => $vendorInstalled ? 'success' : 'failed',
            'output' => $output
        ];
        file_put_contents($logDir . '/project_detection.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
    }
}

// Définir si le projet est prêt pour l'étape suivante
// Pour un projet PHP simple, toujours prêt
// Pour Laravel, vérifier si .env existe
$projectInfo['ready_for_next'] = ($projectInfo['type'] === 'php') || 
                                 ($projectInfo['type'] === 'laravel' && $projectInfo['has_env']);

// Définir le type de contenu
header('Content-Type: application/json');

// Ajouter des informations de débogage
$projectInfo['debug'] = [
    'php_version' => PHP_VERSION,
    'server_info' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
    'current_dir' => __DIR__,
    'project_root_exists' => is_dir($projectInfo['project_root']),
    'project_root_readable' => is_readable($projectInfo['project_root']),
    'project_root_writable' => is_writable($projectInfo['project_root']),
    'session_id' => session_id()
];

// Renvoyer la réponse
echo json_encode($projectInfo);

/**
 * Met à jour le fichier .env avec les données fournies
 * Version locale pour éviter les conflits de déclaration
 *
 * @param array $envData Données à insérer dans le fichier .env
 * @return bool Succès de la mise à jour
 */
function updateEnvFileLocal($envData) {
    $projectRoot = $_SESSION['project_root'];
    $envPath = $projectRoot . '/.env';
    
    // Créer le fichier .env s'il n'existe pas
    if (!file_exists($envPath)) {
        $content = '';
        foreach ($envData as $key => $value) {
            $content .= "{$key}={$value}\n";
        }
        file_put_contents($envPath, $content);
        return true;
    }
    
    // Mettre à jour le fichier .env existant
    $content = file_get_contents($envPath);
    foreach ($envData as $key => $value) {
        $content = preg_replace("/{$key}=.*/", "{$key}={$value}", $content);
    }
    file_put_contents($envPath, $content);
    return true;
}

/**
 * Génère une clé aléatoire pour l'application
 * Version locale pour éviter les conflits de déclaration
 *
 * @return string Clé aléatoire
 */
function generateRandomKeyLocal() {
    $key = '';
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+{}[]:;<>?,./';
    for ($i = 0; $i < 32; $i++) {
        $key .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $key;
}
