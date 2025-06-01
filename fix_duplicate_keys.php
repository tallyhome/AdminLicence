<?php

/**
 * Script pour corriger les clés dupliquées dans les fichiers de traduction JSON
 */

// Liste des fichiers à corriger
$files = [
    'resources/locales/ar/translation.json',
    'resources/locales/ja/translation.json',
    'resources/locales/tr/translation_fixed.json',
    'resources/locales/tr/translation_new.json',
    'resources/locales/tr/translation_temp.json',
    'resources/locales/tr/translation.json',
];

// Fonction pour corriger les clés dupliquées dans un fichier JSON
function fixDuplicateKeys($filePath) {
    echo "Traitement du fichier: $filePath\n";
    
    if (!file_exists($filePath)) {
        echo "Le fichier n'existe pas: $filePath\n";
        return false;
    }
    
    // Créer une copie de sauvegarde
    $backupPath = $filePath . '.bak';
    if (!file_exists($backupPath)) {
        copy($filePath, $backupPath);
        echo "Sauvegarde créée: $backupPath\n";
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    
    // Décoder le JSON
    $decoded = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur JSON dans le fichier $filePath: " . json_last_error_msg() . "\n";
        return false;
    }
    
    // Fonction récursive pour supprimer les clés dupliquées
    function removeDuplicateKeys($array) {
        $result = [];
        
        foreach ($array as $key => $value) {
            // Si la valeur est un tableau, appliquer récursivement
            if (is_array($value)) {
                $result[$key] = removeDuplicateKeys($value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    // Supprimer les clés dupliquées
    $cleanedData = removeDuplicateKeys($decoded);
    
    // Écrire le contenu corrigé
    $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    file_put_contents($filePath, json_encode($cleanedData, $jsonOptions));
    
    // Vérifier si le JSON est maintenant valide
    $content = file_get_contents($filePath);
    $decoded = json_decode($content);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur JSON après correction pour $filePath: " . json_last_error_msg() . "\n";
        return false;
    }
    
    echo "Le fichier $filePath est maintenant un JSON valide\n";
    return true;
}

// Traiter chaque fichier
$baseDir = __DIR__ . '/';
foreach ($files as $file) {
    $fullPath = $baseDir . $file;
    fixDuplicateKeys($fullPath);
    echo "\n";
}

echo "Correction des clés dupliquées terminée.\n";
