<?php

// API de traductions - Solution de contournement
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Récupérer la locale depuis les paramètres GET
$locale = $_GET['locale'] ?? 'fr';

// Langues disponibles
$availableLocales = ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru'];

// Vérifier si la locale est valide
if (!in_array($locale, $availableLocales)) {
    $locale = 'en'; // Fallback vers l'anglais
}

// Chemin vers les fichiers de traduction
$basePath = dirname(dirname(__DIR__)) . '/resources/locales/';
$translationFile = $basePath . $locale . '/translation.json';

$response = [
    'locale' => $locale,
    'translations' => []
];

try {
    if (file_exists($translationFile)) {
        $content = file_get_contents($translationFile);
        $translations = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($translations)) {
            $response['translations'] = $translations;
        } else {
            throw new Exception('Erreur de décodage JSON: ' . json_last_error_msg());
        }
    } else {
        // Essayer avec le fallback anglais
        $fallbackFile = $basePath . 'en/translation.json';
        
        if (file_exists($fallbackFile)) {
            $content = file_get_contents($fallbackFile);
            $translations = json_decode($content, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($translations)) {
                $response['translations'] = $translations;
                $response['locale'] = 'en'; // Indiquer qu'on utilise le fallback
            } else {
                throw new Exception('Erreur de décodage JSON fallback: ' . json_last_error_msg());
            }
        } else {
            throw new Exception('Aucun fichier de traduction trouvé');
        }
    }
} catch (Exception $e) {
    // En cas d'erreur, retourner des traductions minimales
    $response['translations'] = [
        'common' => [
            'dashboard' => 'Dashboard',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'delete' => 'Delete',
            'edit' => 'Edit',
            'loading' => 'Loading...',
            'error' => 'Error',
            'success' => 'Success'
        ]
    ];
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>