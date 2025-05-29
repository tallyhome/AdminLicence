<?php
/**
 * Script de test pour l'API AdminLicence avec une clé API
 * Ce script teste l'API de vérification des licences en utilisant une clé API valide
 */

// Configuration
$apiKey = 'sk_VZwQ9VzvuRwt1nsrIbkKPXgNicuR0Dx1'; // Clé API à utiliser
$serialKey = '9QXH-YDNF-WBFL-XFTU'; // Clé de licence à vérifier
$domain = 'exemple.com';
$ipAddress = '127.0.0.1';

// Endpoint à tester
$endpoint = '/api/v1/check-serial';
$baseUrl = 'http://127.0.0.1:8000';
$url = $baseUrl . $endpoint;

// Fonction pour tester l'API avec une clé API
function testApiWithKey($url, $apiKey, $serialKey, $domain, $ipAddress) {
    // Données à envoyer
    $data = [
        'serial_key' => $serialKey,
        'domain' => $domain,
        'ip_address' => $ipAddress
    ];
    
    // Initialiser cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-API-KEY: ' . $apiKey
    ]);
    
    // Ajouter des options pour éviter les boucles
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout après 10 secondes
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Timeout de connexion après 5 secondes
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Désactiver la vérification SSL (pour dev uniquement)
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Désactiver la vérification du nom d'hôte (pour dev uniquement)
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Exécuter le test si demandé
$result = null;
$testStarted = false;

if (isset($_GET['test'])) {
    $testStarted = true;
    $result = testApiWithKey($url, $apiKey, $serialKey, $domain, $ipAddress);
}

// Afficher la page HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de l'API AdminLicence avec clé API</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .container { max-width: 1200px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        hr { margin: 20px 0; border: 0; border-top: 1px solid #ddd; }
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
        .button { display: inline-block; background: #007bff; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; }
        .button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test de l'API AdminLicence avec clé API</h1>
        
        <div class="card">
            <h2>Configuration du test</h2>
            <p><strong>Clé API:</strong> <?php echo htmlspecialchars($apiKey); ?></p>
            <p><strong>Clé de licence:</strong> <?php echo htmlspecialchars($serialKey); ?></p>
            <p><strong>Domaine:</strong> <?php echo htmlspecialchars($domain); ?></p>
            <p><strong>Adresse IP:</strong> <?php echo htmlspecialchars($ipAddress); ?></p>
            <p><strong>Endpoint testé:</strong> <?php echo htmlspecialchars($endpoint); ?></p>
            
            <a href="?test=1" class="button">Lancer le test</a>
        </div>
        
        <?php if ($testStarted && $result): ?>
            <div class="card">
                <h2>Résultat du test</h2>
                <p><strong>Code HTTP:</strong> <?php echo $result['http_code']; ?></p>
                
                <?php if ($result['error']): ?>
                    <p class="error"><strong>Erreur:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                <?php endif; ?>
                
                <h3>Réponse brute:</h3>
                <pre><?php echo htmlspecialchars($result['response']); ?></pre>
                
                <?php if ($result['response']): ?>
                    <?php $jsonResponse = json_decode($result['response'], true); ?>
                    <?php if (json_last_error() === JSON_ERROR_NONE): ?>
                        <h3>Réponse décodée:</h3>
                        <ul>
                            <?php foreach ($jsonResponse as $key => $value): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($key); ?>:</strong>
                                    <?php if (is_array($value) || is_object($value)): ?>
                                        <pre><?php echo htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)); ?></pre>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($value); ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if (isset($jsonResponse['valid'])): ?>
                            <p>
                                <strong>Statut de la licence:</strong>
                                <?php if ($jsonResponse['valid']): ?>
                                    <span class="success">Valide ✅</span>
                                <?php else: ?>
                                    <span class="error">Non valide ❌</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="error">La réponse n'est pas un JSON valide.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
