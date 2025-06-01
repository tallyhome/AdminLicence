<?php

/**
 * Script pour mettre à jour la clé de licence d'installation dans le fichier .env
 */

// Vérifier si un argument a été fourni
if ($argc < 2) {
    echo "Usage: php update_licence_key.php <licence_key>\n";
    exit(1);
}

// Récupérer la clé de licence fournie en argument
$licenceKey = $argv[1];

// Vérifier si le fichier .env existe
if (!file_exists(__DIR__ . '/.env')) {
    echo "Erreur: Le fichier .env n'existe pas.\n";
    exit(1);
}

// Lire le contenu du fichier .env
$envContent = file_get_contents(__DIR__ . '/.env');

// Vérifier si la variable INSTALLATION_LICENSE_KEY existe déjà
if (preg_match('/^INSTALLATION_LICENSE_KEY=(.*)$/m', $envContent, $matches)) {
    // Remplacer la valeur existante
    $envContent = preg_replace(
        '/^INSTALLATION_LICENSE_KEY=.*$/m',
        'INSTALLATION_LICENSE_KEY=' . $licenceKey,
        $envContent
    );
    echo "La clé de licence d'installation a été mise à jour.\n";
} else {
    // Ajouter la variable si elle n'existe pas
    $envContent .= "\nINSTALLATION_LICENSE_KEY=" . $licenceKey;
    echo "La clé de licence d'installation a été ajoutée.\n";
}

// Écrire le contenu mis à jour dans le fichier .env
file_put_contents(__DIR__ . '/.env', $envContent);

echo "Mise à jour terminée. N'oubliez pas de redémarrer l'application pour appliquer les changements.\n";
echo "Exécutez la commande suivante pour vider les caches :\n";
echo "php artisan cache:clear && php artisan config:clear\n";
