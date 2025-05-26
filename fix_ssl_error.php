<?php
/**
 * Script pour corriger l'erreur SSL PR_END_OF_FILE_ERROR
 */

echo "Correction de l'erreur SSL PR_END_OF_FILE_ERROR...\n";

$envPath = __DIR__ . '/.env';

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // S'assurer que APP_URL utilise bien HTTP et non HTTPS
    if (strpos($envContent, 'APP_URL=https://') !== false) {
        $envContent = str_replace('APP_URL=https://', 'APP_URL=http://', $envContent);
        file_put_contents($envPath, $envContent);
        echo "Le fichier .env a été modifié : APP_URL utilise maintenant HTTP au lieu de HTTPS.\n";
    } else {
        echo "Le fichier .env utilise déjà HTTP, pas de modification nécessaire.\n";
    }
    
    // Afficher la valeur actuelle
    preg_match('/APP_URL=(.*)/', $envContent, $matches);
    if (isset($matches[1])) {
        echo "Valeur actuelle : APP_URL=" . $matches[1] . "\n";
    }
} else {
    echo "Fichier .env introuvable.\n";
}

echo "\nIMPORTANT : Accédez au site avec HTTP et non HTTPS :\n";
echo "http://127.0.0.1:8000/admin\n\n";
echo "Si le problème persiste, arrêtez le serveur actuel et relancez-le avec :\n";
echo "php -S 127.0.0.1:8000 -t public\n";
