<?php
/**
 * Script pour supprimer le dossier d'installation
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir le type de contenu comme JSON
header('Content-Type: application/json');

/**
 * Fonction récursive pour supprimer un dossier et son contenu
 * 
 * @param string $dir Chemin du dossier à supprimer
 * @return bool Succès ou échec de la suppression
 */
function removeDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    // Parcourir tous les fichiers et sous-dossiers
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

// Chemin du dossier d'installation
$installDir = __DIR__ . '/../';

// Essayer de supprimer le dossier d'installation
$success = false;

try {
    // Créer un fichier temporaire pour indiquer que le dossier doit être supprimé
    // (au cas où la suppression immédiate échoue)
    file_put_contents($installDir . 'remove_me.txt', 'Ce dossier peut être supprimé en toute sécurité.');
    
    // Essayer de supprimer le dossier
    $success = removeDirectory($installDir);
} catch (Exception $e) {
    $success = false;
}

// Renvoyer le résultat
echo json_encode([
    'status' => $success,
    'message' => $success ? 'Installation directory removed successfully' : 'Failed to remove installation directory'
]);
