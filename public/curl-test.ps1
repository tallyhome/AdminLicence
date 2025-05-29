Write-Host "Test de l'API AdminLicence avec cURL" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""

$API_KEY = "sk_VZwQ9VzvuRwt1nsrIbkKPXgNicuR0Dx1"
$SERIAL_KEY = "9QXH-YDNF-WBFL-XFTU"
$DOMAIN = "exemple.com"
$IP_ADDRESS = "127.0.0.1"
$API_URL = "http://127.0.0.1:8000/api/check-serial"

Write-Host "Configuration:" -ForegroundColor Yellow
Write-Host "- Clé API: $API_KEY"
Write-Host "- Clé de licence: $SERIAL_KEY"
Write-Host "- Domaine: $DOMAIN"
Write-Host "- Adresse IP: $IP_ADDRESS"
Write-Host "- URL de l'API: $API_URL"
Write-Host ""

Write-Host "Exécution du test..." -ForegroundColor Green
Write-Host ""

$body = @{
    serial_key = $SERIAL_KEY
    domain = $DOMAIN
    ip_address = $IP_ADDRESS
} | ConvertTo-Json

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
    "X-API-KEY" = $API_KEY
}

try {
    $response = Invoke-WebRequest -Uri $API_URL -Method POST -Body $body -Headers $headers -UseBasicParsing
    
    Write-Host "Statut de la réponse: $($response.StatusCode)" -ForegroundColor Green
    Write-Host ""
    Write-Host "En-têtes de la réponse:" -ForegroundColor Yellow
    $response.Headers | Format-Table -AutoSize
    
    Write-Host "Contenu de la réponse:" -ForegroundColor Yellow
    $responseContent = $response.Content | ConvertFrom-Json
    $responseContent | Format-List
    
    if ($responseContent.valid -eq $true) {
        Write-Host "Statut de la licence: Valide ✅" -ForegroundColor Green
    } else {
        Write-Host "Statut de la licence: Non valide ❌" -ForegroundColor Red
    }
} catch {
    Write-Host "Erreur lors de l'exécution de la requête:" -ForegroundColor Red
    Write-Host $_.Exception.Message
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "Code de statut HTTP: $statusCode" -ForegroundColor Red
        
        if ($_.ErrorDetails.Message) {
            Write-Host "Détails de l'erreur:" -ForegroundColor Red
            $errorContent = $_.ErrorDetails.Message | ConvertFrom-Json
            $errorContent | Format-List
        }
    }
}

Write-Host ""
Write-Host "Test terminé." -ForegroundColor Cyan
Read-Host "Appuyez sur Entrée pour continuer..."
