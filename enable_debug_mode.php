<?php

/**
 * Script pour activer le mode debug dans le fichier .env
 */

// Vérifier si le fichier .env existe
if (!file_exists(__DIR__ . '/.env')) {
    echo "Erreur: Le fichier .env n'existe pas.\n";
    exit(1);
}

// Lire le contenu du fichier .env
$envContent = file_get_contents(__DIR__ . '/.env');

// Mettre à jour APP_DEBUG
if (preg_match('/^APP_DEBUG=(.*)$/m', $envContent, $matches)) {
    // Remplacer la valeur existante
    $envContent = preg_replace(
        '/^APP_DEBUG=.*$/m',
        'APP_DEBUG=true',
        $envContent
    );
    echo "Le mode debug a été activé.\n";
} else {
    // Ajouter la variable si elle n'existe pas
    $envContent .= "\nAPP_DEBUG=true";
    echo "La variable APP_DEBUG a été ajoutée avec la valeur true.\n";
}

// Mettre à jour APP_ENV
if (preg_match('/^APP_ENV=(.*)$/m', $envContent, $matches)) {
    // Remplacer la valeur existante
    $envContent = preg_replace(
        '/^APP_ENV=.*$/m',
        'APP_ENV=local',
        $envContent
    );
    echo "L'environnement a été défini sur 'local'.\n";
} else {
    // Ajouter la variable si elle n'existe pas
    $envContent .= "\nAPP_ENV=local";
    echo "La variable APP_ENV a été ajoutée avec la valeur 'local'.\n";
}

// Écrire le contenu mis à jour dans le fichier .env
file_put_contents(__DIR__ . '/.env', $envContent);

echo "Mise à jour terminée. N'oubliez pas de redémarrer l'application pour appliquer les changements.\n";
echo "Exécutez la commande suivante pour vider les caches :\n";
echo "php artisan cache:clear && php artisan config:clear\n";
