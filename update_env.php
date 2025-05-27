<?php

// Script pour mettre à jour le fichier .env avec les éléments manquants
$envFile = __DIR__ . '/.env';
$content = file_get_contents($envFile);

if ($content === false) {
    echo "Erreur: Impossible de lire le fichier .env\n";
    exit(1);
}

// Modifications à apporter
$modifications = [
    // Remplacer l'adresse email
    '/MAIL_FROM_ADDRESS="hello@example.com"/' => 'MAIL_FROM_ADDRESS="noreply@adminlicence.com"',
    
    // Ajouter APP_INSTALLED=true s'il n'existe pas déjà
    '/INSTALLATION_LICENSE_KEY=(.*)/' => "INSTALLATION_LICENSE_KEY=$1\n\nAPP_INSTALLED=true",
    
    // Ajouter MAIL_ENCRYPTION=tls s'il n'existe pas déjà
    '/MAIL_FROM_NAME=(.*)/' => "MAIL_FROM_NAME=$1\nMAIL_ENCRYPTION=tls",
];

// Appliquer les modifications
foreach ($modifications as $pattern => $replacement) {
    // Vérifier si la modification est déjà présente
    if (strpos($pattern, 'APP_INSTALLED') !== false && strpos($content, 'APP_INSTALLED=true') !== false) {
        echo "APP_INSTALLED est déjà présent, pas de modification nécessaire.\n";
        continue;
    }
    
    if (strpos($pattern, 'MAIL_ENCRYPTION') !== false && strpos($content, 'MAIL_ENCRYPTION=tls') !== false) {
        echo "MAIL_ENCRYPTION est déjà présent, pas de modification nécessaire.\n";
        continue;
    }
    
    // Appliquer la modification
    $content = preg_replace($pattern, $replacement, $content, 1, $count);
    
    if ($count > 0) {
        echo "Modification appliquée: " . explode('/', $pattern)[1] . "\n";
    } else {
        echo "Aucune correspondance trouvée pour: " . explode('/', $pattern)[1] . "\n";
    }
}

// Écrire le contenu mis à jour
if (file_put_contents($envFile, $content) !== false) {
    echo "Le fichier .env a été mis à jour avec succès.\n";
} else {
    echo "Erreur: Impossible d'écrire dans le fichier .env\n";
    exit(1);
}

echo "Toutes les modifications ont été appliquées avec succès.\n";
