<?php
/**
 * Script de test simple pour l'API AdminLicence
 * Ce script teste les différents endpoints API pour vérifier le fonctionnement des clés API
 */

// Configuration
$serialKey = '9QXH-YDNF-WBFL-XFTU'; // Clé de licence à vérifier
$domain = 'exemple.com';
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Endpoints à tester
$endpoints = [
    'API Standard' => '/api/check-serial',
    'API v1' => '/api/v1/check-serial',
    'API Standard PHP' => '/api/check-serial.php',
    'API v1 PHP' => '/api/v1/check-serial.php'
];

// Fonction pour tester un endpoint API
function testEndpoint($endpoint, $serialKey, $domain, $ipAddress) {
    $baseUrl = 'http://127.0.0.1:8000';
    $url = $baseUrl . $endpoint;
    
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
        'Accept: application/json'
    ]);
    
    // Ajouter des options pour éviter les boucles
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

// Exécuter les tests si demandé
$results = [];
$testStarted = false;

if (isset($_GET['test'])) {
    $testStarted = true;
    // Limiter le nombre de tentatives pour éviter les boucles infinies
    $maxAttempts = isset($_GET['attempts']) ? (int)$_GET['attempts'] : 0;
    
    if ($maxAttempts < 3) { // Maximum 3 tentatives
        foreach ($endpoints as $name => $endpoint) {
            $results[$name] = testEndpoint($endpoint, $serialKey, $domain, $ipAddress);
            // Ajouter un délai entre les requêtes
            usleep(300000); // 300ms
        }
    } else {
        $results['error'] = [
            'http_code' => 0,
            'response' => '',
            'error' => 'Nombre maximum de tentatives atteint. Veuillez réinitialiser le test.'
        ];
    }
}

// Afficher la page HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test simple de l'API AdminLicence</title>
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
        <h1>Test simple de l'API AdminLicence</h1>
        
        <div class="card">
            <h2>Configuration du test</h2>
            <p><strong>Clé de licence:</strong> <?php echo htmlspecialchars($serialKey); ?></p>
            <p><strong>Domaine:</strong> <?php echo htmlspecialchars($domain); ?></p>
            <p><strong>Adresse IP:</strong> <?php echo htmlspecialchars($ipAddress); ?></p>
            
            <p><em>Note: Ce test n'utilise pas de clé API pour l'authentification. Il teste uniquement les endpoints API publics.</em></p>
            
            <?php if ($testStarted && isset($_GET['attempts']) && (int)$_GET['attempts'] >= 3): ?>
                <a href="test-api-simple.php" class="button">Réinitialiser le test</a>
            <?php else: ?>
                <a href="?test=1&attempts=<?php echo isset($_GET['attempts']) ? ((int)$_GET['attempts'] + 1) : 1; ?>" class="button">Lancer le test</a>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($results)): ?>
            <h2>Résultats des tests</h2>
            
            <?php foreach ($results as $name => $result): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
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
