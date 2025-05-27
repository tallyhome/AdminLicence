<?php
// Script pour ajouter la clé de traduction "serial_keys.search_by_key" dans tous les fichiers de langue

// Liste des langues et leurs traductions pour "Recherche par clé"
$translations = [
    'ar' => 'البحث عن طريق المفتاح',
    'de' => 'Suche nach Schlüssel',
    'en' => 'Search by Key',
    'es' => 'Búsqueda por clave',
    'fr' => 'Recherche par clé',
    'it' => 'Ricerca per chiave',
    'ja' => 'キーで検索',
    'nl' => 'Zoeken op sleutel',
    'pt' => 'Pesquisa por chave',
    'ru' => 'Поиск по ключу',
    'tr' => 'Anahtara göre ara',
    'zh' => '按键搜索'
];

// Parcourir chaque fichier de langue
foreach ($translations as $lang => $translation) {
    $file = __DIR__ . "/{$lang}.json";
    
    if (!file_exists($file)) {
        echo "Le fichier {$lang}.json n'existe pas.\n";
        continue;
    }
    
    // Lire le contenu JSON du fichier
    $content = file_get_contents($file);
    $json = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur de décodage JSON pour {$lang}.json: " . json_last_error_msg() . "\n";
        continue;
    }
    
    // Vérifier si serial_keys existe et est un objet
    if (!isset($json['serial_keys']) || !is_array($json['serial_keys'])) {
        // Créer la structure si elle n'existe pas
        $json['serial_keys'] = [];
    }
    
    // Ajouter ou mettre à jour la clé search_by_key
    $json['serial_keys']['search_by_key'] = $translation;
    
    // Enregistrer le fichier avec les modifications
    $newContent = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $newContent);
    
    echo "Mise à jour de {$lang}.json réussie avec '{$translation}'.\n";
}

// Gérer également les fichiers _fixed s'ils existent
foreach (['en_fixed.json', 'fr_fixed.json'] as $fixedFile) {
    $file = __DIR__ . "/{$fixedFile}";
    
    if (!file_exists($file)) {
        continue;
    }
    
    $lang = substr($fixedFile, 0, 2); // 'en' ou 'fr'
    $translation = $translations[$lang];
    
    $content = file_get_contents($file);
    $json = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur de décodage JSON pour {$fixedFile}: " . json_last_error_msg() . "\n";
        continue;
    }
    
    if (!isset($json['serial_keys']) || !is_array($json['serial_keys'])) {
        $json['serial_keys'] = [];
    }
    
    $json['serial_keys']['search_by_key'] = $translation;
    
    $newContent = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $newContent);
    
    echo "Mise à jour de {$fixedFile} réussie avec '{$translation}'.\n";
}

echo "Mise à jour de tous les fichiers de traduction terminée.\n";
