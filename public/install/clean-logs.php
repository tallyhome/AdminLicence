<?php
/**
 * Script de nettoyage des logs d'installation
 * Ce script archive les anciens logs et ne conserve que les plus récents
 */

// Configuration
$logsDir = __DIR__ . '/logs';
$archiveDir = $logsDir . '/archives';
$maxLogAge = 30; // Jours
$maxLogSize = 1024 * 1024; // 1 Mo
$excludedFiles = ['.htaccess']; // Fichiers à ne jamais supprimer

// Créer le répertoire d'archives s'il n'existe pas
if (!file_exists($archiveDir)) {
    mkdir($archiveDir, 0755, true);
}

// Protéger le répertoire d'archives
file_put_contents($archiveDir . '/.htaccess', "Order Allow,Deny\nDeny from all");

// Fonction pour formater la taille des fichiers
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

// Récupérer tous les fichiers de logs
$logFiles = glob($logsDir . '/*.log');
$totalSize = 0;
$cleanedSize = 0;
$archivedCount = 0;
$deletedCount = 0;

echo "=== Nettoyage des logs d'installation ===\n";
echo "Répertoire: " . $logsDir . "\n";
echo "Nombre de fichiers: " . count($logFiles) . "\n\n";

// Traiter chaque fichier
foreach ($logFiles as $file) {
    $filename = basename($file);
    
    // Ignorer les fichiers exclus
    if (in_array($filename, $excludedFiles)) {
        continue;
    }
    
    $fileSize = filesize($file);
    $totalSize += $fileSize;
    $modTime = filemtime($file);
    $ageInDays = floor((time() - $modTime) / (60 * 60 * 24));
    
    echo "Fichier: $filename\n";
    echo "  Taille: " . formatFileSize($fileSize) . "\n";
    echo "  Âge: $ageInDays jours\n";
    
    // Décider quoi faire avec le fichier
    if ($ageInDays > $maxLogAge || $fileSize > $maxLogSize) {
        // Archiver les fichiers importants
        if (strpos($filename, 'installation_') === 0 || 
            strpos($filename, 'admin_') === 0 || 
            $filename === 'installation.log') {
            
            $archiveFile = $archiveDir . '/' . date('Ymd_', $modTime) . $filename;
            echo "  Action: Archivage vers " . basename($archiveDir) . "/" . basename($archiveFile) . "\n";
            
            if (copy($file, $archiveFile)) {
                unlink($file);
                $archivedCount++;
                $cleanedSize += $fileSize;
            }
        } else {
            // Supprimer les fichiers moins importants
            echo "  Action: Suppression\n";
            if (unlink($file)) {
                $deletedCount++;
                $cleanedSize += $fileSize;
            }
        }
    } else {
        echo "  Action: Conservation\n";
    }
    
    echo "\n";
}

// Créer un fichier vide pour chaque type de log important s'il n'existe pas
$essentialLogs = [
    'installation.log',
    'admin_requests.log',
    'admin_responses.log',
    'installation_complete.log'
];

foreach ($essentialLogs as $logFile) {
    $fullPath = $logsDir . '/' . $logFile;
    if (!file_exists($fullPath)) {
        file_put_contents($fullPath, '');
        echo "Création du fichier de log essentiel: $logFile\n";
    }
}

// Afficher le résumé
echo "=== Résumé du nettoyage ===\n";
echo "Taille totale des logs: " . formatFileSize($totalSize) . "\n";
echo "Espace libéré: " . formatFileSize($cleanedSize) . "\n";
echo "Fichiers archivés: $archivedCount\n";
echo "Fichiers supprimés: $deletedCount\n";
echo "Terminé!\n";
