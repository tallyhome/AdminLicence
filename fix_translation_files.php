<?php

/**
 * Script pour corriger les problèmes dans les fichiers de traduction JSON
 * - Corrige les erreurs de syntaxe
 * - Supprime les clés dupliquées
 */

// Liste des fichiers à corriger
$files = [
    'resources/locales/tr/translation_corrected.json',
    'resources/locales/ar/translation.json',
    'resources/locales/ja/translation.json',
    'resources/locales/tr/translation_fixed.json',
    'resources/locales/tr/translation_new.json',
    'resources/locales/tr/translation_temp.json',
    'resources/locales/tr/translation.json',
];

// Fonction pour corriger un fichier JSON
function fixJsonFile($filePath) {
    echo "Traitement du fichier: $filePath\n";
    
    if (!file_exists($filePath)) {
        echo "Le fichier n'existe pas: $filePath\n";
        return false;
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    
    // Vérifier si le contenu est un JSON valide
    $decoded = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur JSON dans le fichier $filePath: " . json_last_error_msg() . "\n";
        
        // Essayer de corriger les erreurs de syntaxe courantes
        // Vérifier si le fichier se termine correctement
        if (substr(trim($content), -1) !== '}') {
            echo "Correction de l'accolade fermante manquante\n";
            $content = rtrim($content) . "\n}";
        }
        
        // Vérifier à nouveau
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Impossible de corriger automatiquement le fichier $filePath\n";
            return false;
        }
    }
    
    // Fonction récursive pour supprimer les clés dupliquées
    function removeDuplicateKeys($array) {
        $result = [];
        
        foreach ($array as $key => $value) {
            // Si la clé existe déjà, ignorer cette entrée
            if (isset($result[$key])) {
                echo "Clé dupliquée trouvée: $key\n";
                continue;
            }
            
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
    
    // Créer une copie de sauvegarde du fichier original
    $backupPath = $filePath . '.backup';
    if (!file_exists($backupPath)) {
        copy($filePath, $backupPath);
        echo "Sauvegarde créée: $backupPath\n";
    }
    
    // Écrire le contenu corrigé
    $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    file_put_contents($filePath, json_encode($cleanedData, $jsonOptions));
    
    echo "Fichier corrigé: $filePath\n";
    return true;
}

// Traiter chaque fichier
$baseDir = __DIR__ . '/';
foreach ($files as $file) {
    $fullPath = $baseDir . $file;
    fixJsonFile($fullPath);
    echo "\n";
}

echo "Correction des fichiers de traduction terminée.\n";
