<?php

/**
 * Script pour vider le cache des traductions
 * À exécuter lorsque les traductions ne sont pas correctement chargées
 */

// Définir le chemin vers l'application Laravel
$basePath = dirname(__DIR__);

// Inclure l'autoloader de Composer
require $basePath . '/vendor/autoload.php';

// Charger le framework Laravel
$app = require_once $basePath . '/bootstrap/app.php';

// Démarrer le conteneur d'application
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Vider le cache des traductions pour toutes les langues disponibles
$locales = ['en', 'fr', 'de', 'es', 'it', 'pt', 'nl', 'ru', 'zh', 'ja', 'tr', 'ar'];

foreach ($locales as $locale) {
    $cacheKey = 'translations.' . $locale;
    if (Illuminate\Support\Facades\Cache::has($cacheKey)) {
        Illuminate\Support\Facades\Cache::forget($cacheKey);
        echo "Cache vidé pour la langue : " . $locale . PHP_EOL;
    } else {
        echo "Aucun cache trouvé pour la langue : " . $locale . PHP_EOL;
    }
}

echo PHP_EOL . "Toutes les traductions ont été rechargées avec succès !" . PHP_EOL;

// Script d'aplatissement des traductions

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$langPath = resource_path('lang');
$outputPath = $langPath;

// Fonction pour aplatir un tableau associatif
function flattenArray(array $array, string $prefix = ''): array {
    $result = [];
    
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            // Si c'est un tableau, appel récursif avec préfixe
            $result = array_merge($result, flattenArray($value, $prefix . $key . '.'));
        } else {
            // Sinon, ajouter la clé préfixée et la valeur
            $result[$prefix . $key] = $value;
        }
    }
    
    return $result;
}

// Pour chaque fichier JSON dans le dossier lang
$jsonFiles = glob($langPath . '/*.json');
foreach ($jsonFiles as $jsonFile) {
    $locale = basename($jsonFile, '.json');
    
    // Lire le contenu JSON
    $content = file_get_contents($jsonFile);
    $translations = json_decode($content, true);
    
    if (is_array($translations)) {
        // Aplatir le tableau
        $flatTranslations = flattenArray($translations);
        
        // Créer un fichier temporaire
        $newFile = $outputPath . '/' . $locale . '.new.json';
        file_put_contents($newFile, json_encode($flatTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Remplacer l'ancien fichier
        rename($newFile, $jsonFile);
        echo "Translations flattened for {$locale}.\n";
    }
}

// Vider le cache Laravel
if (function_exists('artisan')) {
    artisan('cache:clear');
    artisan('view:clear');
    artisan('route:clear');
    artisan('config:clear');
    echo "All Laravel caches cleared.\n";
}

echo "Done.\n";
