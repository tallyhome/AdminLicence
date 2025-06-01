<?php

/**
 * Script pour corriger tous les problèmes dans les fichiers de traduction JSON
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
    
    // Créer une copie de sauvegarde
    $backupPath = $filePath . '.backup';
    if (!file_exists($backupPath)) {
        copy($filePath, $backupPath);
        echo "Sauvegarde créée: $backupPath\n";
    }
    
    // Lire le contenu du fichier ligne par ligne
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    
    // Cas spécial pour translation_corrected.json (erreur de syntaxe)
    if (basename($filePath) === 'translation_corrected.json') {
        // Reconstruire le fichier JSON correctement
        $newContent = "{\n";
        $inString = false;
        $level = 0;
        $skipNextChar = false;
        
        // Parcourir chaque ligne du fichier
        foreach ($lines as $line) {
            // Ignorer les lignes vides à la fin
            if (trim($line) === '') continue;
            
            // Ajouter la ligne au nouveau contenu
            $newContent .= $line . "\n";
        }
        
        // S'assurer que le fichier se termine correctement
        $lastLine = trim(end($lines));
        if ($lastLine === '},') {
            // Remplacer la dernière virgule par une accolade fermante
            $newContent = substr($newContent, 0, -3) . "\n}\n";
        } elseif ($lastLine !== '}') {
            // Ajouter l'accolade fermante si elle manque
            $newContent .= "}\n";
        }
        
        // Écrire le contenu corrigé
        file_put_contents($filePath, $newContent);
        echo "Fichier réécrit avec structure JSON corrigée: $filePath\n";
    } else {
        // Pour les autres fichiers, corriger les clés dupliquées
        
        // Lire le contenu JSON
        $content = file_get_contents($filePath);
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
        
        echo "Clés dupliquées potentiellement corrigées pour $filePath\n";
    }
    
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
    fixJsonFile($fullPath);
    echo "\n";
}

echo "Correction des fichiers de traduction terminée.\n";
