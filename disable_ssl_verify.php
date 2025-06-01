<?php

/**
 * Script pour désactiver la vérification SSL dans le fichier .env
 */

// Vérifier si le fichier .env existe
if (!file_exists(__DIR__ . '/.env')) {
    echo "Erreur: Le fichier .env n'existe pas.\n";
    exit(1);
}

// Lire le contenu du fichier .env
$envContent = file_get_contents(__DIR__ . '/.env');

// Mettre à jour la variable SECURITY_ENABLE_SSL_VERIFY
if (preg_match('/^SECURITY_ENABLE_SSL_VERIFY=(.*)$/m', $envContent, $matches)) {
    // Remplacer la valeur existante
    $envContent = preg_replace(
        '/^SECURITY_ENABLE_SSL_VERIFY=.*$/m',
        'SECURITY_ENABLE_SSL_VERIFY=false',
        $envContent
    );
    echo "La vérification SSL a été désactivée.\n";
} else {
    // Ajouter la variable si elle n'existe pas
    $envContent .= "\nSECURITY_ENABLE_SSL_VERIFY=false";
    echo "La variable SECURITY_ENABLE_SSL_VERIFY a été ajoutée avec la valeur false.\n";
}

// Écrire le contenu mis à jour dans le fichier .env
file_put_contents(__DIR__ . '/.env', $envContent);

echo "Mise à jour terminée. N'oubliez pas de redémarrer l'application pour appliquer les changements.\n";
echo "Exécutez la commande suivante pour vider les caches :\n";
echo "php artisan cache:clear && php artisan config:clear\n";
