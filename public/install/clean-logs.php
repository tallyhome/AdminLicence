<?php
/**
 * Script de nettoyage des logs d'installation et Laravel
 * Ce script archive les anciens logs et ne conserve que les plus récents
 * 
 * Options:
 * --delete-all : Supprime tous les fichiers de logs (sauf les essentiels qui seront vidés)
 * --type=install : Nettoie uniquement les logs d'installation
 * --type=laravel : Nettoie uniquement les logs Laravel
 */

// Analyser les arguments
$deleteAll = in_array('--delete-all', $argv);
$logType = 'all';

foreach ($argv as $arg) {
    if (strpos($arg, '--type=') === 0) {
        $logType = substr($arg, 7);
    }
}

// Chemin vers la racine du projet (2 niveaux au-dessus de public/install)
$rootPath = dirname(dirname(__DIR__));

// Configuration
$installLogsDir = __DIR__ . '/logs';
$laravelLogsDir = $rootPath . '/storage/logs';
$archiveDir = $installLogsDir . '/archives';
$maxLogAge = 30; // Jours
$maxLogSize = 1024 * 1024; // 1 Mo
$excludedFiles = ['.htaccess']; // Fichiers à ne jamais supprimer

// Créer le répertoire d'archives s'il n'existe pas
if (!file_exists($archiveDir)) {
    mkdir($archiveDir, 0755, true);
}

// Protéger le répertoire d'archives
file_put_contents($archiveDir . '/.htaccess', "Order Allow,Deny\nDeny from all");

// Vérifier que le répertoire des logs Laravel existe
if (!file_exists($laravelLogsDir)) {
    mkdir($laravelLogsDir, 0755, true);
}

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

// Initialiser les compteurs
$totalSize = 0;
$cleanedSize = 0;
$archivedCount = 0;
$deletedCount = 0;

echo "=== Nettoyage des logs ===\n";
if ($deleteAll) {
    echo "Mode: SUPPRESSION COMPLÈTE\n";
}
echo "Type de logs: " . ($logType === 'all' ? 'Tous' : ($logType === 'install' ? 'Installation' : 'Laravel')) . "\n\n";

// Traiter les logs d'installation si demandé
if ($logType === 'all' || $logType === 'install') {
    processLogs($installLogsDir, $archiveDir, 'Installation', $deleteAll, $maxLogAge, $maxLogSize, $excludedFiles, $totalSize, $cleanedSize, $archivedCount, $deletedCount);
}

// Traiter les logs Laravel si demandé
if ($logType === 'all' || $logType === 'laravel') {
    processLogs($laravelLogsDir, $archiveDir, 'Laravel', $deleteAll, $maxLogAge, $maxLogSize, $excludedFiles, $totalSize, $cleanedSize, $archivedCount, $deletedCount);
}

/**
 * Traite les fichiers de logs dans un répertoire donné
 */
function processLogs($logsDir, $archiveDir, $logTypeLabel, $deleteAll, $maxLogAge, $maxLogSize, $excludedFiles, &$totalSize, &$cleanedSize, &$archivedCount, &$deletedCount) {
    // Récupérer tous les fichiers de logs
    $logFiles = glob($logsDir . '/*.log');
    
    echo "=== Traitement des logs $logTypeLabel ===\n";
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
        if ($deleteAll) {
            // En mode suppression complète
            $isEssential = false;
            
            // Pour les logs d'installation, certains fichiers sont essentiels
            if ($logTypeLabel === 'Installation') {
                $isEssential = in_array($filename, [
                    'installation.log',
                    'admin_requests.log',
                    'admin_responses.log',
                    'installation_complete.log'
                ]);
            }
            // Pour Laravel, laravel.log est essentiel
            else if ($logTypeLabel === 'Laravel') {
                $isEssential = ($filename === 'laravel.log');
            }
            
            if ($isEssential) {
                // Vider les fichiers essentiels au lieu de les supprimer
                echo "  Action: Vidage (fichier essentiel)\n";
                if (file_put_contents($file, '') !== false) {
                    $cleanedSize += $fileSize;
                }
            } else {
                // Supprimer les autres fichiers
                echo "  Action: Suppression\n";
                if (unlink($file)) {
                    $deletedCount++;
                    $cleanedSize += $fileSize;
                }
            }
        } else if ($ageInDays > $maxLogAge || $fileSize > $maxLogSize) {
            // Mode normal - Archiver les fichiers importants
            $isImportant = false;
            
            // Pour les logs d'installation
            if ($logTypeLabel === 'Installation') {
                $isImportant = strpos($filename, 'installation_') === 0 || 
                               strpos($filename, 'admin_') === 0 || 
                               $filename === 'installation.log';
            }
            // Pour Laravel
            else if ($logTypeLabel === 'Laravel') {
                $isImportant = $filename === 'laravel.log';
            }
            
            if ($isImportant) {
                $archiveFile = $archiveDir . '/' . date('Ymd_', $modTime) . $logTypeLabel . '_' . $filename;
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
}

// Créer un fichier vide pour chaque type de log important s'il n'existe pas
function createEssentialLogs() {
    global $installLogsDir, $laravelLogsDir, $logType;
    
    // Logs d'installation essentiels
    if ($logType === 'all' || $logType === 'install') {
        $installEssentialLogs = [
            'installation.log',
            'admin_requests.log',
            'admin_responses.log',
            'installation_complete.log'
        ];
        
        foreach ($installEssentialLogs as $logFile) {
            $fullPath = $installLogsDir . '/' . $logFile;
            if (!file_exists($fullPath)) {
                file_put_contents($fullPath, '');
                echo "Création du fichier de log essentiel d'installation: $logFile\n";
            }
        }
    }
    
    // Log Laravel essentiel
    if ($logType === 'all' || $logType === 'laravel') {
        $laravelLog = $laravelLogsDir . '/laravel.log';
        if (!file_exists($laravelLog)) {
            file_put_contents($laravelLog, '');
            echo "Création du fichier de log Laravel essentiel: laravel.log\n";
        }
    }
}

// Créer les logs essentiels
createEssentialLogs();

// Afficher le résumé
echo "\n=== Résumé du nettoyage ===\n";
echo "Taille totale des logs: " . formatFileSize($totalSize) . "\n";
echo "Espace libéré: " . formatFileSize($cleanedSize) . "\n";
echo "Fichiers archivés: $archivedCount\n";
echo "Fichiers supprimés: $deletedCount\n";
echo "Terminé!\n";
