<?php
/**
 * AJAX handler for saving admin account information
 */

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

// Journaliser la requête
$requestLog = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'save_admin',
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $_POST,
];
file_put_contents($logDir . '/admin_requests.log', json_encode($requestLog, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Check if admin information is provided
if (!isset($_POST['project_url']) || !isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
    echo json_encode([
        'status' => false,
        'message' => 'All fields are required',
    ]);
    exit;
}

// Check if passwords match
if ($_POST['password'] !== $_POST['confirm_password']) {
    echo json_encode([
        'status' => false,
        'message' => 'Passwords do not match',
    ]);
    exit;
}

$adminConfig = [
    'project_url' => $_POST['project_url'],
    'email' => $_POST['email'],
    'password' => $_POST['password'],
];

// Store admin configuration in session
$_SESSION['admin_config'] = $adminConfig;
$_SESSION['admin_saved'] = true;

// Mettre à jour le fichier .env avec l'URL du projet
$projectRoot = $_SESSION['project_root'] ?? realpath(__DIR__ . '/../../../');
$envPath = $projectRoot . '/.env';

// Créer le répertoire de logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Journaliser la mise à jour du fichier .env
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'update_env_app_url',
    'project_url' => $adminConfig['project_url'],
    'env_path' => $envPath
];

// Vérifier si le fichier .env existe
if (file_exists($envPath)) {
    // Lire le contenu du fichier .env
    $envContent = file_get_contents($envPath);
    
    // Mettre à jour l'URL de l'application
    $envContent = preg_replace('/APP_URL=.*/', 'APP_URL=' . $adminConfig['project_url'], $envContent);
    
    // Écrire le fichier .env mis à jour
    $updated = file_put_contents($envPath, $envContent) !== false;
    
    $logData['status'] = $updated ? 'success' : 'failed';
} else {
    $logData['status'] = 'error';
    $logData['message'] = 'Fichier .env introuvable';
}

file_put_contents($logDir . '/admin_config.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Préparer la réponse
$response = [
    'status' => true,
    'message' => 'Admin account information saved',
    'admin_email' => $adminConfig['email'],
    'project_url' => $adminConfig['project_url'],
    'session_data' => [
        'admin_saved' => $_SESSION['admin_saved'] ?? false,
        'admin_config' => isset($_SESSION['admin_config']) ? 'Set' : 'Not set'
    ]
];

// Journaliser la réponse
$responseLog = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'save_admin_response',
    'response' => $response
];
file_put_contents($logDir . '/admin_responses.log', json_encode($responseLog, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

// Renvoyer la réponse
echo json_encode($response);
