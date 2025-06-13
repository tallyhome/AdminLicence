<?php
// Vérifier si l'application est installée
$isInstalled = false;

// Vérifier si le fichier .env existe
if (file_exists(__DIR__ . '/.env')) {
    // Lire le contenu du fichier .env
    $envContent = file_get_contents(__DIR__ . '/.env');
    
    // Vérifier si l'application est marquée comme installée
    if (strpos($envContent, 'APP_INSTALLED=true') !== false) {
        $isInstalled = true;
    }
}

// Rediriger vers l'installation ou l'application
if (!$isInstalled) {
    header('Location: /install/index.php?step=1');
} else {
    header('Location: /public');
}
exit;