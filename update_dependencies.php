<?php
/**
 * Script de mise à jour des dépendances pour AdminLicence
 * Ce script analyse le fichier composer.json et met à jour les versions des dépendances
 * pour éviter les vulnérabilités potentielles
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

// Versions recommandées pour les packages Laravel 12
$recommendedVersions = [
    'php' => '^8.2',
    'laravel/framework' => '^12.0',
    'laravel/sanctum' => '^4.0',
    'laravel/tinker' => '^2.8',
    'spatie/laravel-ignition' => '^2.0',
    'pragmarx/google2fa' => '^8.0',
    'pragmarx/google2fa-laravel' => '^2.0',
    'bacon/bacon-qr-code' => '^2.0',
    'erusev/parsedown' => '^1.7',
    'laravel/ui' => '^4.3',
    // Dépendances de développement
    'fakerphp/faker' => '^1.23.0',
    'laravel/pail' => '^1.2.2',
    'laravel/pint' => '^1.13',
    'laravel/sail' => '^1.26',
    'laravel/telescope' => '^5.0',
    'mockery/mockery' => '^1.6.6',
    'phpunit/phpunit' => '^10.5'
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
    echo "Exécutez maintenant la commande suivante pour mettre à jour les dépendances:\n";
    echo "composer update --no-scripts\n";
} else {
    echo "\nToutes les dépendances sont déjà à jour avec les versions recommandées.\n";
}

// Vérifier les problèmes SSL
echo "\nVérification des problèmes SSL avec Composer...\n";
echo "Si vous rencontrez des problèmes SSL avec Composer, essayez les solutions suivantes:\n";
echo "1. Mettre à jour les certificats CA: composer config --global cafile C:/path/to/cacert.pem\n";
echo "2. Désactiver temporairement la vérification SSL (non recommandé pour la production): composer config --global disable-tls true\n";
echo "3. Utiliser un miroir alternatif: composer config --global repo.packagist composer https://packagist.org\n";

// Recommandations de sécurité supplémentaires
echo "\nRecommandations de sécurité supplémentaires:\n";
echo "1. Exécutez régulièrement 'composer audit' pour vérifier les vulnérabilités connues\n";
echo "2. Utilisez GitHub Dependabot ou Snyk pour surveiller automatiquement les dépendances\n";
echo "3. Limitez l'accès aux dépendances sensibles en utilisant des autorisations strictes\n";
echo "4. Vérifiez les journaux de sécurité des packages principaux régulièrement\n";
