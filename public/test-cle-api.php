<?php
/**
 * Script de test pour vérifier le fonctionnement des clés API dans AdminLicence
 */

// Configuration
$apiUrl = 'http://127.0.0.1:8000'; // URL de base de l'API
$apiKey = 'sk_Tc53GD4UycO0kMyQs9UZrBTVFKAWpIxz'; // Clé API existante
$serialKey = '9QXH-YDNF-WBFL-XFTU'; // Clé de licence à vérifier
$domain = 'exemple.com';
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Fonction pour tester l'API
function testApi($apiUrl, $endpoint, $apiKey, $serialKey, $domain, $ipAddress) {
    $data = [
        'serial_key' => $serialKey,
        'domain' => $domain,
        'ip_address' => $ipAddress
    ];
    
    $headers = [
        'Content-Type: application/json',
        'X-API-KEY: ' . $apiKey
    ];
    
    $ch = curl_init($apiUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
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

// Exécuter les tests si demandé
$results = [];
$endpoints = ['/api/v1/check-serial', '/api/check-serial'];

if (isset($_GET['test'])) {
    foreach ($endpoints as $endpoint) {
        $results[$endpoint] = testApi($apiUrl, $endpoint, $apiKey, $serialKey, $domain, $ipAddress);
    }
}

// Afficher la page HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des clés API - AdminLicence</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .container { max-width: 1200px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        hr { margin: 20px 0; border: 0; border-top: 1px solid #ddd; }
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test des clés API - AdminLicence</h1>
        
        <div class="card">
            <h2>Configuration du test</h2>
            <p><strong>URL de l'API:</strong> <?php echo htmlspecialchars($apiUrl); ?></p>
            <p><strong>Clé API:</strong> <?php echo htmlspecialchars($apiKey); ?></p>
            <p><strong>Clé de licence:</strong> <?php echo htmlspecialchars($serialKey); ?></p>
            <p><strong>Domaine:</strong> <?php echo htmlspecialchars($domain); ?></p>
            <p><strong>Adresse IP:</strong> <?php echo htmlspecialchars($ipAddress); ?></p>
            
            <a href="?test=1" class="button">Lancer le test</a>
        </div>
        
        <?php if (!empty($results)): ?>
            <h2>Résultats des tests</h2>
            
            <?php foreach ($results as $endpoint => $result): ?>
                <div class="card">
                    <h3>Endpoint: <?php echo htmlspecialchars($endpoint); ?></h3>
                    <p><strong>Code HTTP:</strong> <?php echo $result['http_code']; ?></p>
                    
                    <?php if ($result['error']): ?>
                        <p class="error"><strong>Erreur:</strong> <?php echo htmlspecialchars($result['error']); ?></p>
                    <?php endif; ?>
                    
                    <h4>Réponse brute:</h4>
                    <pre><?php echo htmlspecialchars($result['response']); ?></pre>
                    
                    <?php if ($result['response']): ?>
                        <?php $jsonResponse = json_decode($result['response'], true); ?>
                        <?php if (json_last_error() === JSON_ERROR_NONE): ?>
                            <h4>Réponse décodée:</h4>
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
