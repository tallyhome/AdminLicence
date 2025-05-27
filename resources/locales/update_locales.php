<?php
// Script pour ajouter la clé de traduction "search_by_key" dans tous les fichiers de traduction du répertoire locales

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

// Fonction pour chercher récursivement dans le fichier JSON une clé spécifique
function findKeyPath($array, $key, $currentPath = '') {
    foreach ($array as $k => $v) {
        $path = $currentPath ? "$currentPath.$k" : $k;
        
        // Si c'est un tableau associatif et qu'il contient des clés liées aux serial_keys
        if (is_array($v) && (strpos($k, 'serial') !== false || strpos($path, 'serial') !== false)) {
            echo "Trouvé une section possible pour serial_keys: $path\n";
        }
        
        // Si on trouve la clé
        if ($k === $key) {
            return $path;
        }
        
        // Si c'est un tableau, on cherche récursivement
        if (is_array($v)) {
            $result = findKeyPath($v, $key, $path);
            if ($result) {
                return $result;
            }
        }
    }
    
    return null;
}

// Parcourir chaque langue
foreach ($translations as $lang => $translation) {
    $translationFile = __DIR__ . "/{$lang}/translation.json";
    
    if (!file_exists($translationFile)) {
        echo "Le fichier {$translationFile} n'existe pas.\n";
        continue;
    }
    
    // Lire le contenu JSON du fichier
    $content = file_get_contents($translationFile);
    $json = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur de décodage JSON pour {$translationFile}: " . json_last_error_msg() . "\n";
        continue;
    }
    
    // Chercher une section appropriée pour ajouter notre clé
    echo "Analyse du fichier {$lang}/translation.json pour trouver où ajouter la clé...\n";
    findKeyPath($json, 'serial_keys');
    
    // Cherchons une section pour les clés de série
    if (!isset($json['serial_keys'])) {
        // Si aucune section spécifique n'existe, on la crée
        $json['serial_keys'] = [
            'search_by_key' => $translation
        ];
        echo "Création d'une nouvelle section 'serial_keys' dans {$lang}/translation.json\n";
    } else {
        // Si la section existe, on ajoute notre clé
        $json['serial_keys']['search_by_key'] = $translation;
        echo "Ajout de la clé 'search_by_key' à la section existante dans {$lang}/translation.json\n";
    }
    
    // Enregistrer le fichier avec les modifications
    $newContent = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($translationFile, $newContent);
    
    echo "Mise à jour de {$lang}/translation.json réussie avec '{$translation}'.\n";
}

echo "Mise à jour de tous les fichiers de traduction terminée.\n";
