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
