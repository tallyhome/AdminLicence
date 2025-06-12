<?php

// Test direct des traductions sans Laravel
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$locale = $_GET['locale'] ?? 'fr';

// Chemin vers les fichiers de traduction
$basePath = dirname(__DIR__) . '/resources/locales/';
$translationFile = $basePath . $locale . '/translation.json';

$response = [
    'status' => 'success',
    'locale' => $locale,
    'file_path' => $translationFile,
    'file_exists' => file_exists($translationFile),
    'translations' => []
];

if (file_exists($translationFile)) {
    $content = file_get_contents($translationFile);
    $translations = json_decode($content, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        $response['translations'] = $translations;
        $response['message'] = 'Traductions chargées avec succès';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Erreur de décodage JSON: ' . json_last_error_msg();
    }
} else {
    // Essayer avec le fallback anglais
    $fallbackFile = $basePath . 'en/translation.json';
    $response['fallback_file'] = $fallbackFile;
    $response['fallback_exists'] = file_exists($fallbackFile);
    
    if (file_exists($fallbackFile)) {
        $content = file_get_contents($fallbackFile);
        $translations = json_decode($content, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            $response['translations'] = $translations;
            $response['message'] = 'Traductions fallback chargées avec succès';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Erreur de décodage JSON fallback: ' . json_last_error_msg();
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Aucun fichier de traduction trouvé';
        $response['translations'] = [
            'common' => [
                'dashboard' => 'Dashboard',
                'save' => 'Save',
                'cancel' => 'Cancel'
            ]
        ];
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>