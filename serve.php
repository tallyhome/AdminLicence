<?php
/**
 * Script pour démarrer un serveur de développement pour Laravel 12
 * 
 * Ce script remplace la commande "php artisan serve" qui n'est plus disponible
 * dans Laravel 12 par défaut.
 */

// Vérifier que le script est exécuté en ligne de commande
if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.");
}

// Paramètres par défaut
$host = '127.0.0.1';
$port = '8000';

// Analyser les arguments de ligne de commande
$options = getopt('', ['host::', 'port::']);
if (isset($options['host'])) {
    $host = $options['host'];
}
if (isset($options['port'])) {
    $port = $options['port'];
}

echo "Démarrage du serveur de développement Laravel sur http://{$host}:{$port}\n";
echo "Appuyez sur Ctrl+C pour arrêter le serveur.\n\n";

// Vérifier si le dossier public existe
$publicPath = __DIR__ . '/public';
if (!is_dir($publicPath)) {
    die("Erreur : Le dossier 'public' n'existe pas.\n");
}

// Démarrer le serveur PHP intégré avec notre routeur personnalisé
$routerPath = __DIR__ . '/router.php';
if (!file_exists($routerPath)) {
    die("Erreur : Le fichier 'router.php' n'existe pas. Veuillez le créer d'abord.\n");
}

$command = "php -S {$host}:{$port} -t " . escapeshellarg($publicPath) . " " . escapeshellarg($routerPath);
echo "Exécution de la commande : {$command}\n\n";
passthru($command);
