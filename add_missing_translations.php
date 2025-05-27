<?php

// Script pour ajouter les clés de traduction manquantes à tous les fichiers de traduction
$languages = [
    'en' => [
        'settings.two_factor.status.disabled' => 'Disabled',
        'settings.two_factor.status.enabled' => 'Enabled',
        'language.changed_successfully' => 'Language changed successfully'
    ],
    'fr' => [
        'settings.two_factor.status.disabled' => 'Désactivée',
        'settings.two_factor.status.enabled' => 'Activée',
        'language.changed_successfully' => 'Langue changée avec succès'
    ],
    'es' => [
        'settings.two_factor.status.disabled' => 'Desactivada',
        'settings.two_factor.status.enabled' => 'Activada',
        'language.changed_successfully' => 'Idioma cambiado con éxito'
    ],
    'de' => [
        'settings.two_factor.status.disabled' => 'Deaktiviert',
        'settings.two_factor.status.enabled' => 'Aktiviert',
        'language.changed_successfully' => 'Sprache erfolgreich geändert'
    ],
    'it' => [
        'settings.two_factor.status.disabled' => 'Disabilitata',
        'settings.two_factor.status.enabled' => 'Abilitata',
        'language.changed_successfully' => 'Lingua cambiata con successo'
    ],
    'pt' => [
        'settings.two_factor.status.disabled' => 'Desativada',
        'settings.two_factor.status.enabled' => 'Ativada',
        'language.changed_successfully' => 'Idioma alterado com sucesso'
    ],
    'nl' => [
        'settings.two_factor.status.disabled' => 'Uitgeschakeld',
        'settings.two_factor.status.enabled' => 'Ingeschakeld',
        'language.changed_successfully' => 'Taal succesvol gewijzigd'
    ],
    'ar' => [
        'settings.two_factor.status.disabled' => 'معطل',
        'settings.two_factor.status.enabled' => 'مفعل',
        'language.changed_successfully' => 'تم تغيير اللغة بنجاح'
    ],
    'ja' => [
        'settings.two_factor.status.disabled' => '無効',
        'settings.two_factor.status.enabled' => '有効',
        'language.changed_successfully' => '言語が正常に変更されました'
    ],
    'ru' => [
        'settings.two_factor.status.disabled' => 'Отключено',
        'settings.two_factor.status.enabled' => 'Включено',
        'language.changed_successfully' => 'Язык успешно изменен'
    ],
    'tr' => [
        'settings.two_factor.status.disabled' => 'Devre dışı',
        'settings.two_factor.status.enabled' => 'Etkin',
        'language.changed_successfully' => 'Dil başarıyla değiştirildi'
    ],
    'zh' => [
        'settings.two_factor.status.disabled' => '已禁用',
        'settings.two_factor.status.enabled' => '已启用',
        'language.changed_successfully' => '语言更改成功'
    ]
];

// Fonction pour ajouter les clés de traduction manquantes à un fichier de traduction
function addMissingTranslations($filePath, $translations) {
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    if (!$content) {
        echo "Erreur lors de la lecture du fichier: $filePath\n";
        return false;
    }

    // Décoder le contenu JSON
    $jsonData = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur lors du décodage JSON: " . json_last_error_msg() . " dans $filePath\n";
        
        // Approche alternative: ajouter les clés manuellement
        echo "Tentative d'ajout manuel des clés...\n";
        
        // Pour chaque clé de traduction
        foreach ($translations as $key => $value) {
            // Vérifier si la clé existe déjà
            if (strpos($content, "\"$key\"") === false) {
                // Trouver un bon endroit pour insérer la nouvelle clé (avant la dernière accolade)
                $lastBracePos = strrpos($content, '}');
                if ($lastBracePos === false) {
                    echo "Impossible de trouver la dernière accolade.\n";
                    continue;
                }
                
                // Insérer la nouvelle clé
                $content = substr($content, 0, $lastBracePos) . ",\n    \"$key\": \"$value\"\n" . substr($content, $lastBracePos);
                
                echo "Clé $key ajoutée manuellement.\n";
            }
        }
        
        // Écrire le contenu mis à jour
        if (file_put_contents($filePath, $content)) {
            echo "Mise à jour manuelle réussie: $filePath\n";
            return true;
        } else {
            echo "Erreur lors de l'écriture dans le fichier: $filePath\n";
            return false;
        }
    }

    // Ajouter les clés manquantes
    $updated = false;
    foreach ($translations as $key => $value) {
        // Séparer la clé en parties
        $keyParts = explode('.', $key);
        
        // Créer la structure si elle n'existe pas
        $current = &$jsonData;
        foreach ($keyParts as $i => $part) {
            if ($i === count($keyParts) - 1) {
                // Dernière partie de la clé
                if (!isset($current[$part])) {
                    $current[$part] = $value;
                    $updated = true;
                    echo "Clé $key ajoutée.\n";
                }
            } else {
                // Partie intermédiaire de la clé
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }
    }

    if (!$updated) {
        echo "Aucune nouvelle clé à ajouter dans: $filePath\n";
        return true;
    }

    // Encoder le contenu JSON avec formatage
    $newContent = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur lors de l'encodage JSON: " . json_last_error_msg() . " dans $filePath\n";
        return false;
    }

    // Écrire le contenu mis à jour
    if (file_put_contents($filePath, $newContent)) {
        echo "Mise à jour réussie: $filePath\n";
        return true;
    } else {
        echo "Erreur lors de l'écriture dans le fichier: $filePath\n";
        return false;
    }
}

// Parcourir les langues et mettre à jour les fichiers
$baseDir = __DIR__ . '/resources/locales/';
foreach ($languages as $lang => $translations) {
    $filePath = $baseDir . $lang . '/translation.json';
    if (file_exists($filePath)) {
        echo "Mise à jour du fichier $lang...\n";
        addMissingTranslations($filePath, $translations);
    } else {
        echo "Le fichier $filePath n'existe pas.\n";
    }
}

echo "Mise à jour des traductions terminée.\n";
