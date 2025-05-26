<?php
/**
 * Script de correction des problèmes de configuration et de syntaxe
 * 
 * Ce script va :
 * 1. Corriger les problèmes de syntaxe dans les fichiers PHP
 * 2. Mettre à jour la configuration pour éviter les erreurs SSL
 * 3. Vider tous les caches
 */

echo "===================================================\n";
echo "Script de correction de l'application AdminLicence\n";
echo "===================================================\n\n";

// Définir les chemins
$basePath = __DIR__;
$envPath = $basePath . '/.env';

// 1. Vérifier et corriger les problèmes dans DashboardController.php
echo "[1/5] Vérification du fichier DashboardController.php...\n";
$dashboardControllerPath = $basePath . '/app/Http/Controllers/Admin/DashboardController.php';

if (file_exists($dashboardControllerPath)) {
    $content = file_get_contents($dashboardControllerPath);
    
    // Remplacer toutes les utilisations non qualifiées de Setting::
    $content = preg_replace('/(?<!\\\\App\\\\Models\\\\)Setting::/', '\\App\\Models\\Setting::', $content);
    
    file_put_contents($dashboardControllerPath, $content);
    echo "   - Correction des références à la classe Setting: OK\n";
} else {
    echo "   - Fichier non trouvé: ÉCHEC\n";
}

// 2. Mettre à jour le fichier .env pour éviter les erreurs SSL
echo "[2/5] Mise à jour du fichier .env...\n";

if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    
    // Définir APP_ENV sur local pour éviter les problèmes SSL pendant le développement
    $envContent = preg_replace('/^APP_ENV=.*$/m', 'APP_ENV=local', $envContent);
    
    // S'assurer que APP_URL utilise HTTP et non HTTPS
    $envContent = preg_replace('/^APP_URL=.*$/m', 'APP_URL=http://127.0.0.1:8000', $envContent);
    
    // S'assurer que les paramètres de cache et broadcast sont définis
    if (!preg_match('/^BROADCAST_DRIVER=/m', $envContent)) {
        $envContent .= "\nBROADCAST_DRIVER=log\n";
    }
    
    if (!preg_match('/^CACHE_DRIVER=/m', $envContent)) {
        $envContent .= "CACHE_DRIVER=file\n";
    }
    
    file_put_contents($envPath, $envContent);
    echo "   - Mise à jour du fichier .env: OK\n";
} else {
    echo "   - Fichier .env non trouvé: ÉCHEC\n";
}

// 3. Correction du middleware CheckLicenseMiddleware.php
echo "[3/5] Vérification du middleware CheckLicenseMiddleware.php...\n";
$middlewarePath = $basePath . '/app/Http/Middleware/CheckLicenseMiddleware.php';

if (file_exists($middlewarePath)) {
    $content = file_get_contents($middlewarePath);
    
    // Remplacer les références non qualifiées à Setting
    $content = preg_replace('/(?<!\\\\App\\\\Models\\\\)Setting::/', '\\App\\Models\\Setting::', $content);
    
    file_put_contents($middlewarePath, $content);
    echo "   - Correction des références dans le middleware: OK\n";
} else {
    echo "   - Fichier middleware non trouvé: ÉCHEC\n";
}

// 4. Correction du service LicenceService.php
echo "[4/5] Vérification du service LicenceService.php...\n";
$servicePath = $basePath . '/app/Services/LicenceService.php';

if (file_exists($servicePath)) {
    $content = file_get_contents($servicePath);
    
    // Remplacer les références non qualifiées à Setting
    $content = preg_replace('/(?<!\\\\App\\\\Models\\\\)Setting::/', '\\App\\Models\\Setting::', $content);
    
    file_put_contents($servicePath, $content);
    echo "   - Correction des références dans le service: OK\n";
} else {
    echo "   - Fichier service non trouvé: ÉCHEC\n";
}

// 5. Vidage des caches
echo "[5/5] Nettoyage des caches...\n";

// Vider le cache d'application
if (file_exists($basePath . '/bootstrap/cache/config.php')) {
    @unlink($basePath . '/bootstrap/cache/config.php');
    echo "   - Cache de configuration supprimé\n";
}

if (file_exists($basePath . '/bootstrap/cache/routes.php')) {
    @unlink($basePath . '/bootstrap/cache/routes.php');
    echo "   - Cache de routes supprimé\n";
}

// Vider le cache de vues
$viewsCachePath = $basePath . '/storage/framework/views';
if (is_dir($viewsCachePath)) {
    $files = glob($viewsCachePath . '/*.php');
    foreach ($files as $file) {
        @unlink($file);
    }
    echo "   - Cache de vues supprimé\n";
}

echo "\n=============================================\n";
echo "Toutes les corrections ont été appliquées !\n";
echo "=============================================\n\n";

echo "Pour lancer le serveur en mode développement, exécutez :\n";
echo "php -S 127.0.0.1:8000 -t public\n\n";

echo "N'oubliez pas de vérifier que le fichier .env contient la bonne clé de licence :\n";
echo "INSTALLATION_LICENSE_KEY=votre-clé-de-licence\n";
echo "Clé actuelle : " . (getenv('INSTALLATION_LICENSE_KEY') ?: "Non définie") . "\n\n";
