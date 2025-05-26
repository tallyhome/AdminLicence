<?php

/**
 * Script pour vérifier et mettre à jour le fichier .env
 * Ce script compare le fichier .env actuel avec un ensemble standard de variables
 * et ajoute les variables manquantes.
 */

// Chemin vers le fichier .env
$envPath = __DIR__ . '/.env';

// Vérifier si le fichier .env existe
if (!file_exists($envPath)) {
    echo "Le fichier .env n'existe pas. Création d'un nouveau fichier...\n";
    touch($envPath);
}

// Lire le contenu actuel du fichier .env
$currentEnv = file_get_contents($envPath);

// Liste des variables standard qui devraient être présentes dans le fichier .env
$standardEnvVars = [
    'APP_NAME' => 'AdminLicence',
    'APP_ENV' => 'local',
    'APP_KEY' => '',
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
    
    'BROADCAST_DRIVER' => 'log',
    'CACHE_DRIVER' => 'file',
    'FILESYSTEM_DISK' => 'local',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'file',
    'SESSION_LIFETIME' => '120',
    
    'MEMCACHED_HOST' => '127.0.0.1',
    
    'REDIS_HOST' => '127.0.0.1',
    'REDIS_PASSWORD' => 'null',
    'REDIS_PORT' => '6379',
    
    'MAIL_MAILER' => 'log',
    'MAIL_SCHEME' => 'null',
    'MAIL_HOST' => '127.0.0.1',
    'MAIL_PORT' => '2525',
    'MAIL_USERNAME' => 'null',
    'MAIL_PASSWORD' => 'null',
    'MAIL_FROM_ADDRESS' => 'hello@example.com',
    'MAIL_FROM_NAME' => '${APP_NAME}',
    
    'AWS_ACCESS_KEY_ID' => '',
    'AWS_SECRET_ACCESS_KEY' => '',
    'AWS_DEFAULT_REGION' => 'us-east-1',
    'AWS_BUCKET' => '',
    'AWS_USE_PATH_STYLE_ENDPOINT' => 'false',
    
    'VITE_APP_NAME' => '${APP_NAME}',
    
    'INSTALLATION_LICENSE_KEY' => '5YS8-NIBM-PJGC-MWHY'
];

// Vérifier quelles variables sont déjà présentes dans le fichier .env
$missingVars = [];
$existingVars = [];

foreach ($standardEnvVars as $key => $value) {
    if (!preg_match("/^{$key}=/m", $currentEnv)) {
        $missingVars[$key] = $value;
    } else {
        $existingVars[] = $key;
    }
}

// Afficher les variables existantes
echo "Variables d'environnement existantes :\n";
foreach ($existingVars as $key) {
    echo "- {$key}\n";
}

// Afficher les variables manquantes
if (count($missingVars) > 0) {
    echo "\nVariables d'environnement manquantes :\n";
    foreach ($missingVars as $key => $value) {
        echo "- {$key}\n";
    }
    
    // Demander à l'utilisateur s'il souhaite ajouter les variables manquantes
    echo "\nVoulez-vous ajouter ces variables au fichier .env ? (o/n) : ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    
    if (strtolower($line) === 'o' || strtolower($line) === 'oui') {
        // Ajouter les variables manquantes au fichier .env
        $envContent = $currentEnv;
        
        foreach ($missingVars as $key => $value) {
            $envContent .= "\n{$key}={$value}";
        }
        
        // Écrire le contenu mis à jour dans le fichier .env
        file_put_contents($envPath, $envContent);
        
        echo "Les variables manquantes ont été ajoutées au fichier .env.\n";
    } else {
        echo "Aucune modification n'a été apportée au fichier .env.\n";
    }
} else {
    echo "\nToutes les variables standard sont présentes dans le fichier .env.\n";
}

// Vérifier si la clé APP_KEY est définie
if (preg_match("/^APP_KEY=$/m", $currentEnv) || preg_match("/^APP_KEY=base64:$/m", $currentEnv)) {
    echo "\nAttention : La clé APP_KEY n'est pas définie. Voulez-vous générer une nouvelle clé ? (o/n) : ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    
    if (strtolower($line) === 'o' || strtolower($line) === 'oui') {
        // Générer une nouvelle clé
        $key = 'base64:' . base64_encode(random_bytes(32));
        
        // Mettre à jour la clé dans le fichier .env
        $envContent = preg_replace("/^APP_KEY=.*$/m", "APP_KEY={$key}", $currentEnv);
        file_put_contents($envPath, $envContent);
        
        echo "Une nouvelle clé APP_KEY a été générée et ajoutée au fichier .env.\n";
    }
}

echo "\nVérification du fichier .env terminée.\n";
