# Script PowerShell pour nettoyer les fichiers temporaires et de sauvegarde
# Créé le 01/06/2025

# Définir le répertoire de base du projet
$projectRoot = $PSScriptRoot

# Liste des fichiers de script temporaires à supprimer
$tempScripts = @(
    "fix_tr_syntax.php",
    "fix_tr_corrected.php",
    "fix_translation_files.php",
    "fix_translations.php",
    "fix_simple.php",
    "fix_json_files.php",
    "fix_duplicate_keys.php",
    "fix_all_json.php",
    "fix_json.ps1"
)

# Liste des fichiers de sauvegarde à supprimer
$backupFiles = @(
    "resources\locales\tr\translation_temp.json.bak",
    "resources\locales\tr\translation_new.json.bak",
    "resources\locales\tr\translation_fixed.json.bak",
    "resources\locales\tr\translation_corrected.json.bak",
    "resources\locales\tr\translation.json.bak",
    "resources\locales\ja\translation.json.bak",
    "resources\locales\ar\translation.json.bak",
    "app\Http\Controllers\Admin\DashboardController.php.bak"
)

# Liste des fichiers temporaires potentiels à supprimer
$tempFiles = @(
    "resources\locales\tr\translation_temp.json",
    "resources\locales\tr\translation_new.json",
    "resources\locales\tr\translation_fixed.json"
)

# Fonction pour supprimer un fichier avec confirmation
function Remove-FileWithConfirmation {
    param (
        [string]$filePath
    )
    
    $fullPath = Join-Path -Path $projectRoot -ChildPath $filePath
    
    if (Test-Path $fullPath) {
        Write-Host "Suppression du fichier: $fullPath" -ForegroundColor Yellow
        Remove-Item -Path $fullPath -Force
        Write-Host "✓ Fichier supprimé avec succès" -ForegroundColor Green
    } else {
        Write-Host "× Fichier non trouvé: $fullPath" -ForegroundColor Gray
    }
}

# Afficher l'en-tête
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "  NETTOYAGE DES FICHIERS TEMPORAIRES - ADMINLICENCE" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host ""

# Supprimer les scripts temporaires
Write-Host "SUPPRESSION DES SCRIPTS TEMPORAIRES:" -ForegroundColor Blue
foreach ($script in $tempScripts) {
    Remove-FileWithConfirmation -filePath $script
}
Write-Host ""

# Supprimer les fichiers de sauvegarde
Write-Host "SUPPRESSION DES FICHIERS DE SAUVEGARDE:" -ForegroundColor Blue
foreach ($backup in $backupFiles) {
    Remove-FileWithConfirmation -filePath $backup
}
Write-Host ""

# Supprimer les fichiers temporaires potentiels
Write-Host "SUPPRESSION DES FICHIERS TEMPORAIRES:" -ForegroundColor Blue
foreach ($temp in $tempFiles) {
    Remove-FileWithConfirmation -filePath $temp
}
Write-Host ""

# Résumé
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "  NETTOYAGE TERMINÉ" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan

# Supprimer ce script lui-même à la fin (optionnel, commenté par défaut)
# Write-Host "Ce script de nettoyage va s'auto-supprimer..." -ForegroundColor Yellow
# $selfPath = $MyInvocation.MyCommand.Path
# Remove-Item -Path $selfPath -Force
