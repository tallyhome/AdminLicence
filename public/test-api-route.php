<?php
/**
 * Script de test simple pour vérifier si les routes API sont accessibles
 */

// Configuration
$baseUrl = 'http://127.0.0.1:8000';

// Endpoints à tester
$endpoints = [
    'Test API' => '/api/test',
    'Test API v1' => '/api/v1/test'
];

// Fonction pour tester un endpoint API
function testEndpoint($url) {
    // Initialiser cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout après 5 secondes
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Timeout de connexion après 3 secondes
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

// Exécuter les tests
$results = [];
foreach ($endpoints as $name => $endpoint) {
    $url = $baseUrl . $endpoint;
    $results[$name] = testEndpoint($url);
    // Ajouter un délai entre les requêtes
    usleep(300000); // 300ms
}

// Afficher la page HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test des routes API AdminLicence</title>
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
        <h1>Test des routes API AdminLicence</h1>
        
        <p>Ce script teste l'accessibilité des routes API de base pour vérifier si le serveur est correctement configuré.</p>
        
        <h2>Résultats des tests</h2>
        
        <?php foreach ($results as $name => $result): ?>
            <div class="card">
                <h3><?php echo htmlspecialchars($name); ?></h3>
                <p><strong>URL testée:</strong> <?php echo htmlspecialchars($baseUrl . $endpoints[$name]); ?></p>
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
                    <?php else: ?>
                        <p class="error">La réponse n'est pas un JSON valide.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
