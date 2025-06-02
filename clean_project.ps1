# Script de nettoyage du projet AdminLicence
# Ce script supprime les fichiers temporaires, logs et fichiers de correction

# Fonction pour afficher les messages
function Write-Log {
    param (
        [string]$Message,
        [string]$Type = "INFO"
    )
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "[$timestamp] [$Type] $Message"
}

Write-Log "Début du nettoyage du projet AdminLicence" "INFO"

# Définir le chemin du projet
$projectPath = $PSScriptRoot

# 1. Supprimer les fichiers de logs
Write-Log "Suppression des fichiers de logs..." "INFO"
$logFiles = @(
    "public\install\debug.log",
    "public\install\logs\installation_complete.log",
    "public\install\logs\installation.log",
    "public\install\logs\admin_responses.log",
    "public\install\logs\admin_requests.log"
)

foreach ($file in $logFiles) {
    $fullPath = Join-Path -Path $projectPath -ChildPath $file
    if (Test-Path $fullPath) {
        Remove-Item -Path $fullPath -Force
        Write-Log "Supprimé: $file" "SUCCESS"
    } else {
        Write-Log "Fichier non trouvé: $file" "WARNING"
    }
}

# 2. Supprimer les fichiers temporaires
Write-Log "Suppression des fichiers temporaires..." "INFO"
$tempFiles = Get-ChildItem -Path $projectPath -Recurse -File | Where-Object {
    $_.Extension -eq ".temp" -or 
    $_.Extension -eq ".backup" -or 
    $_.Name -like "*.json.temp" -or 
    $_.Name -like "*.json.backup"
}

foreach ($file in $tempFiles) {
    Remove-Item -Path $file.FullName -Force
    Write-Log "Supprimé: $($file.FullName.Replace($projectPath, ''))" "SUCCESS"
}

# 3. Supprimer les fichiers de correction
Write-Log "Suppression des fichiers de correction..." "INFO"
$fixFiles = @(
    "fix_ssl_error.php",
    "fix_config.php"
)

foreach ($file in $fixFiles) {
    $fullPath = Join-Path -Path $projectPath -ChildPath $file
    if (Test-Path $fullPath) {
        Remove-Item -Path $fullPath -Force
        Write-Log "Supprimé: $file" "SUCCESS"
    } else {
        Write-Log "Fichier non trouvé: $file" "WARNING"
    }
}

# 4. Supprimer les fichiers de traduction temporaires ou corrigés
Write-Log "Suppression des fichiers de traduction temporaires..." "INFO"
$translationFiles = Get-ChildItem -Path "$projectPath\resources\locales" -Recurse -File | Where-Object {
    $_.Name -like "*_fixed.json*" -or 
    $_.Name -like "*_temp.json*" -or 
    $_.Name -like "*_new.json*" -or
    $_.Name -like "*_corrected*.json*" -or
    $_.Name -like "*.json.temp" -or
    $_.Name -like "*.json.backup"
}

foreach ($file in $translationFiles) {
    Remove-Item -Path $file.FullName -Force
    Write-Log "Supprimé: $($file.FullName.Replace($projectPath, ''))" "SUCCESS"
}

# 5. Nettoyer les fichiers de langue corrigés
Write-Log "Suppression des fichiers de langue corrigés..." "INFO"
$langFiles = Get-ChildItem -Path "$projectPath\resources\lang" -Recurse -File | Where-Object {
    $_.Name -like "*_fixed.json"
}

foreach ($file in $langFiles) {
    Remove-Item -Path $file.FullName -Force
    Write-Log "Supprimé: $($file.FullName.Replace($projectPath, ''))" "SUCCESS"
}

Write-Log "Nettoyage du projet terminé avec succès!" "INFO"
