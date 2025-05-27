<?php
// Script pour mettre à jour tous les fichiers de traduction en ajoutant la clé "serial_keys.search_by_key"

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
    
    // Vérifier si le fichier existe
    if (!file_exists($file)) {
        echo "Le fichier {$lang}.json n'existe pas.\n";
        continue;
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur lors du décodage du fichier {$lang}.json: " . json_last_error_msg() . "\n";
        continue;
    }
    
    // Vérifier si la structure serial_keys existe déjà
    if (!isset($data['serial_keys']) || !is_array($data['serial_keys'])) {
        $data['serial_keys'] = [
            'title' => $lang === 'fr' ? 'Clés de série' : 'Serial Keys',
            'create_key' => $lang === 'fr' ? 'Créer une clé' : 'Create Key'
        ];
    }
    
    // Ajouter ou mettre à jour la clé search_by_key
    $data['serial_keys']['search_by_key'] = $translation;
    
    // Réécrire le fichier
    $newContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $newContent);
    
    echo "Mise à jour du fichier {$lang}.json réussie.\n";
}

echo "Mise à jour de tous les fichiers de traduction terminée.\n";
