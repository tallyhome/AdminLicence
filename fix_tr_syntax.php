<?php

// Fichier à corriger
$filePath = __DIR__ . '/resources/locales/tr/translation_corrected.json';

// Lire le contenu du fichier
$content = file_get_contents($filePath);

// Créer une copie de sauvegarde
copy($filePath, $filePath . '.bak');

// Corriger l'erreur de syntaxe (accolade fermante manquante)
$lastChar = substr(trim($content), -1);
if ($lastChar === ',') {
    // Remplacer la virgule finale par une accolade fermante
    $content = rtrim($content);
    $content = substr($content, 0, -1) . "\n}";
    file_put_contents($filePath, $content);
    echo "Correction effectuée: virgule finale remplacée par accolade fermante\n";
} elseif ($lastChar !== '}') {
    // Ajouter l'accolade fermante manquante
    $content = rtrim($content) . "\n}";
    file_put_contents($filePath, $content);
    echo "Correction effectuée: ajout de l'accolade fermante manquante\n";
} else {
    echo "Le fichier semble déjà correctement formaté\n";
}

// Vérifier si le JSON est valide
$decoded = json_decode($content);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Erreur JSON après correction: " . json_last_error_msg() . "\n";
} else {
    echo "Le fichier JSON est maintenant valide\n";
}
