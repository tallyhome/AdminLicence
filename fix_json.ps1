# Script PowerShell pour vérifier et corriger les fichiers JSON

# Liste des fichiers à traiter
$files = @(
    "resources\locales\ar\translation.json",
    "resources\locales\ja\translation.json",
    "resources\locales\tr\translation_fixed.json",
    "resources\locales\tr\translation_new.json",
    "resources\locales\tr\translation_temp.json",
    "resources\locales\tr\translation.json"
)

foreach ($file in $files) {
    $fullPath = Join-Path -Path $PSScriptRoot -ChildPath $file
    Write-Host "Traitement du fichier: $fullPath"
    
    # Vérifier si le fichier existe
    if (-not (Test-Path $fullPath)) {
        Write-Host "Le fichier n'existe pas: $fullPath" -ForegroundColor Red
        continue
    }
    
    # Créer une copie de sauvegarde
    $backupPath = "$fullPath.bak"
    if (-not (Test-Path $backupPath)) {
        Copy-Item -Path $fullPath -Destination $backupPath
        Write-Host "Sauvegarde créée: $backupPath" -ForegroundColor Green
    }
    
    # Lire le contenu du fichier
    $content = Get-Content -Path $fullPath -Raw
    
    # Tenter de convertir le JSON
    try {
        $jsonObject = $content | ConvertFrom-Json
        Write-Host "Le fichier JSON est valide" -ForegroundColor Green
        
        # Convertir en JSON formaté et réécrire le fichier
        $formattedJson = $jsonObject | ConvertTo-Json -Depth 100
        $formattedJson | Out-File -FilePath $fullPath -Encoding UTF8
        Write-Host "Fichier réécrit avec formatage JSON: $fullPath" -ForegroundColor Green
    }
    catch {
        Write-Host "Erreur JSON dans le fichier $fullPath : $_" -ForegroundColor Red
    }
    
    Write-Host ""
}

Write-Host "Traitement des fichiers JSON terminé." -ForegroundColor Cyan
