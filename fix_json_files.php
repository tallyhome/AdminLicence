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

// Fonction pour corriger un fichier JSON avec erreur de syntaxe
function fixJsonSyntax($filePath) {
    echo "Correction de la syntaxe du fichier: $filePath\n";
    
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
    
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    
    // Cas spécifique pour translation_corrected.json
    if (basename($filePath) === 'translation_corrected.json') {
        // Réécrire complètement le fichier avec une structure JSON valide
        $fixedContent = file_get_contents($backupPath);
        
        // Vérifier si le fichier se termine correctement
        if (substr(trim($fixedContent), -1) !== '}') {
            $fixedContent = rtrim($fixedContent) . "\n}";
        }
        
        file_put_contents($filePath, $fixedContent);
        echo "Fichier réécrit avec accolade fermante: $filePath\n";
        
        // Vérifier si le JSON est maintenant valide
        $decoded = json_decode($fixedContent);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Erreur JSON après correction: " . json_last_error_msg() . "\n";
            return false;
        }
        
        echo "Syntaxe JSON corrigée pour $filePath\n";
        return true;
    }
    
    return true;
}

// Fonction pour corriger les clés dupliquées dans un fichier JSON
function fixDuplicateKeys($filePath) {
    echo "Correction des clés dupliquées dans: $filePath\n";
    
    if (!file_exists($filePath)) {
        echo "Le fichier n'existe pas: $filePath\n";
        return false;
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    
    // Créer un fichier temporaire pour la correction
    $tempFile = $filePath . '.temp';
    
    // Utiliser jq pour supprimer les clés dupliquées (si disponible)
    exec("jq '.' " . escapeshellarg($filePath) . " > " . escapeshellarg($tempFile), $output, $returnVar);
    
    if ($returnVar === 0) {
        // jq a fonctionné, remplacer le fichier original
        rename($tempFile, $filePath);
        echo "Clés dupliquées corrigées avec jq pour $filePath\n";
        return true;
    } else {
        // jq n'est pas disponible ou a échoué, utiliser une méthode alternative
        echo "jq n'est pas disponible ou a échoué, utilisation d'une méthode alternative\n";
        
        // Méthode alternative: réécrire le fichier JSON en utilisant json_decode/json_encode
        // Cela ne préservera pas l'ordre des clés mais supprimera les doublons
        $decoded = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Erreur JSON lors de la lecture: " . json_last_error_msg() . "\n";
            return false;
        }
        
        $jsonOptions = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        file_put_contents($filePath, json_encode($decoded, $jsonOptions));
        
        echo "Clés dupliquées potentiellement corrigées pour $filePath\n";
        return true;
    }
}

// Traiter chaque fichier
$baseDir = __DIR__ . '/';
foreach ($files as $file) {
    $fullPath = $baseDir . $file;
    
    // Corriger d'abord la syntaxe
    if (fixJsonSyntax($fullPath)) {
        // Puis corriger les clés dupliquées
        fixDuplicateKeys($fullPath);
    }
    
    echo "\n";
}

echo "Correction des fichiers de traduction terminée.\n";
