<?php

/**
 * Script pour mettre à jour le fichier .env avec les variables de sécurité manquantes
 * 
 * Ce script ajoute les variables de sécurité manquantes au fichier .env
 * sans écraser les valeurs existantes.
 */

// Vérifier si le fichier .env existe
if (!file_exists(__DIR__ . '/.env')) {
    echo "Erreur: Le fichier .env n'existe pas. Veuillez créer un fichier .env basé sur .env.example.\n";
    exit(1);
}

// Lire le contenu du fichier .env
$envContent = file_get_contents(__DIR__ . '/.env');

// Variables de sécurité à ajouter si elles n'existent pas déjà
$securityVars = [
    'SECURITY_ENABLE_SSL_VERIFY' => 'true',
    'SECURITY_RATE_LIMIT_ATTEMPTS' => '5',
    'SECURITY_RATE_LIMIT_DECAY_MINUTES' => '1',
    'SECURITY_TOKEN_EXPIRY_MINUTES' => '60',
    'SECURITY_ENCRYPT_LICENCE_KEYS' => 'true',
    'SECURITY_ENABLE_AUDIT_LOG' => 'true',
    'SECURITY_TOKEN_SECRET' => generateRandomString(32),
    'SECURITY_ENABLE_CSP' => 'true',
    'SECURITY_ENABLE_XSS_PROTECTION' => 'true',
    'SECURITY_ENABLE_CONTENT_TYPE_OPTIONS' => 'true',
    'SECURITY_ENABLE_FRAME_OPTIONS' => 'true',
    'SECURITY_ENABLE_STRICT_TRANSPORT' => 'true',
    'FORCE_HTTPS' => 'true'
];

// Variables ajoutées
$addedVars = [];

// Vérifier chaque variable et l'ajouter si elle n'existe pas
foreach ($securityVars as $key => $value) {
    if (!preg_match('/^' . preg_quote($key, '/') . '=/m', $envContent)) {
        $envContent .= "\n{$key}={$value}";
        $addedVars[] = $key;
    }
}

// Écrire le contenu mis à jour dans le fichier .env
file_put_contents(__DIR__ . '/.env', $envContent);

// Afficher un résumé des modifications
echo "Mise à jour du fichier .env terminée.\n\n";

if (count($addedVars) > 0) {
    echo "Variables de sécurité ajoutées :\n";
    foreach ($addedVars as $var) {
        echo "- {$var}\n";
    }
} else {
    echo "Aucune variable de sécurité n'a été ajoutée. Toutes les variables nécessaires étaient déjà présentes.\n";
}

echo "\nPour appliquer ces modifications, redémarrez l'application.\n";
echo "Pour vérifier la configuration de sécurité, exécutez : php artisan security:audit\n";

/**
 * Génère une chaîne aléatoire pour les secrets
 * 
 * @param int $length Longueur de la chaîne à générer
 * @return string
 */
function generateRandomString($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_=+';
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $randomString;
}
