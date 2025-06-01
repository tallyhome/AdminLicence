<?php
/**
 * Script pour mettre à jour les packages Composer directement dans le fichier composer.json
 */

// Chemin vers le fichier composer.json
$composerJsonPath = __DIR__ . '/composer.json';

// Vérifier si le fichier existe
if (!file_exists($composerJsonPath)) {
    die("Erreur: Le fichier composer.json n'existe pas.\n");
}

// Lire le contenu du fichier composer.json
$composerJson = file_get_contents($composerJsonPath);
$composer = json_decode($composerJson, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur: Impossible de décoder le fichier composer.json: " . json_last_error_msg() . "\n");
}

// Créer une sauvegarde du fichier composer.json
$backupPath = $composerJsonPath . '.backup-' . date('Y-m-d-His');
file_put_contents($backupPath, $composerJson);
echo "Sauvegarde du fichier composer.json créée: " . basename($backupPath) . "\n\n";

// Versions recommandées pour les packages à mettre à jour
$recommendedVersions = [
    // Dépendances directes
    'bacon/bacon-qr-code' => '^3.0',
    'phpunit/phpunit' => '^11.0',
    
    // Autres dépendances importantes à mettre à jour
    'spatie/flare-client-php' => '^2.0',
    'spatie/error-solutions' => '^2.0'
];

// Mettre à jour les versions des packages
$updated = false;

// Mettre à jour les dépendances principales
foreach ($composer['require'] as $package => $version) {
    if (isset($recommendedVersions[$package]) && $version !== $recommendedVersions[$package]) {
        echo "Mise à jour de {$package}: {$version} -> {$recommendedVersions[$package]}\n";
        $composer['require'][$package] = $recommendedVersions[$package];
        $updated = true;
    }
}

// Mettre à jour les dépendances de développement
foreach ($composer['require-dev'] as $package => $version) {
    if (isset($recommendedVersions[$package]) && $version !== $recommendedVersions[$package]) {
        echo "Mise à jour de {$package}: {$version} -> {$recommendedVersions[$package]}\n";
        $composer['require-dev'][$package] = $recommendedVersions[$package];
        $updated = true;
    }
}

// Écrire les modifications dans le fichier composer.json
if ($updated) {
    $newComposerJson = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    file_put_contents($composerJsonPath, $newComposerJson);
    echo "\nLe fichier composer.json a été mis à jour avec les versions recommandées.\n";
    
    // Recommandations pour l'installation
    echo "\nPour installer les nouvelles dépendances, vous pouvez essayer les options suivantes:\n";
    echo "1. Utiliser Composer avec l'option --ignore-platform-reqs:\n";
    echo "   composer update --ignore-platform-reqs\n\n";
    echo "2. Utiliser Composer avec l'option --no-plugins:\n";
    echo "   composer update --no-plugins\n\n";
    echo "3. Utiliser Docker pour exécuter Composer (si Docker est installé):\n";
    echo "   docker run --rm -v %cd%:/app composer update\n";
} else {
    echo "\nAucune mise à jour n'a été effectuée dans le fichier composer.json.\n";
}

// Vérification des vulnérabilités connues
echo "\nPour vérifier les vulnérabilités connues dans vos dépendances, exécutez:\n";
echo "composer audit\n\n";

// Recommandations de sécurité supplémentaires
echo "Recommandations de sécurité supplémentaires:\n";
echo "1. Vérifiez régulièrement les mises à jour de sécurité pour Laravel et PHP\n";
echo "2. Utilisez des outils comme GitHub Dependabot ou Snyk pour surveiller automatiquement les dépendances\n";
echo "3. Assurez-vous que toutes vos routes API sont correctement protégées\n";
echo "4. Vérifiez que la validation des entrées utilisateur est appliquée partout\n";
echo "5. Assurez-vous que les clés de licence sont correctement chiffrées\n";
