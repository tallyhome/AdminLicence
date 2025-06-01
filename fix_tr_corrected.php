<?php

// Fichier à corriger
$filePath = __DIR__ . '/resources/locales/tr/translation_corrected.json';

// Lire le contenu du fichier
$content = file_get_contents($filePath);

// Créer une copie de sauvegarde
copy($filePath, $filePath . '.backup');

// Corriger l'erreur de syntaxe (accolade fermante manquante)
if (substr(trim($content), -1) !== '}') {
    $content = rtrim($content) . "\n}";
    file_put_contents($filePath, $content);
    echo "Correction de l'accolade fermante manquante dans $filePath\n";
} else {
    echo "Le fichier $filePath semble déjà correctement formaté.\n";
}

// Vérifier si le JSON est valide
$decoded = json_decode($content);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Erreur JSON après correction: " . json_last_error_msg() . "\n";
} else {
    echo "Le fichier JSON est maintenant valide.\n";
}
