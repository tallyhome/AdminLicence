<?php
/**
 * Script pour récupérer les détails de l'administrateur
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

// Vérifier si les informations de l'administrateur sont disponibles
if (!isset($_SESSION['admin_config']) || empty($_SESSION['admin_config'])) {
    echo json_encode([
        'status' => false,
        'message' => 'Admin configuration is missing'
    ]);
    exit;
}

// Récupérer les informations de l'administrateur
$adminConfig = $_SESSION['admin_config'];

// Récupérer l'URL du projet
$projectUrl = $adminConfig['project_url'] ?? '';
if (empty($projectUrl)) {
    // Essayer de récupérer l'URL du projet depuis le fichier .env
    $envPath = realpath(__DIR__ . '/../../../.env');
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        preg_match('/APP_URL=([^\n]+)/', $envContent, $matches);
        $projectUrl = $matches[1] ?? '';
    }
    
    // Si toujours vide, utiliser l'URL actuelle
    if (empty($projectUrl)) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $projectUrl = $protocol . '://' . $host;
    }
}

// Nettoyer l'URL du projet (supprimer les barres obliques à la fin)
$projectUrl = rtrim($projectUrl, '/');

// Construire les URL d'administration et d'utilisateur
$adminUrl = $projectUrl;
$userUrl = $projectUrl;

// Masquer partiellement le mot de passe pour des raisons de sécurité
$passwordHint = $adminConfig['password'];
if (strlen($passwordHint) > 4) {
    $visiblePart = substr($passwordHint, 0, 4);
    $hiddenPart = str_repeat('*', strlen($passwordHint) - 4);
    $passwordHint = $visiblePart . $hiddenPart;
}

// Renvoyer les détails de l'administrateur
echo json_encode([
    'status' => true,
    'email' => $adminConfig['email'],
    'password_hint' => $passwordHint,
    'admin_url' => $adminUrl,
    'user_url' => $userUrl
]);
