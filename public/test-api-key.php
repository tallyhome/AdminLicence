<?php
/**
 * Script de test pour vérifier le fonctionnement des clés API dans AdminLicence
 * Ce script permet de tester la vérification d'une licence via l'API
 */

// Configuration
$apiUrl = 'http://127.0.0.1:8000'; // URL de base de l'API
$apiKey = 'sk_Tc53GD4UycO0kMyQs9UZrBTVFKAWpIxz'; // Remplacez par votre clé API existante
$apiSecret = ''; // Laissez vide si vous ne connaissez pas le secret (il n'est affiché qu'une seule fois à la création)

// Clé de licence à vérifier (remplacez par une clé valide de votre système)
$serialKey = '9QXH-YDNF-WBFL-XFTU'; // Exemple de clé, à remplacer par une clé valide

// Informations de domaine et IP pour le test
$domain = 'exemple.com';
$ipAddress = $_SERVER['REMOTE_ADDR'];

// Fonction pour tester l'API de vérification de licence
function testLicenseVerification($apiUrl, $apiKey, $apiSecret, $serialKey, $domain, $ipAddress) {
    echo "<h2>Test de vérification de licence via l'API</h2>";
    
    // Données à envoyer
    $data = [
        'serial_key' => $serialKey,
        'domain' => $domain,
        'ip_address' => $ipAddress
    ];
    
    // En-têtes HTTP avec authentification par clé API
    $headers = [
        'Content-Type: application/json',
        'X-API-KEY: ' . $apiKey
    ];
    
    // Ajouter le secret si disponible
    if (!empty($apiSecret)) {
        $headers[] = 'X-API-SECRET: ' . $apiSecret;
    }
    
    // Endpoints à tester
    $endpoints = [
        '/api/v1/check-serial',
        '/api/check-serial'
    ];
    
    foreach ($endpoints as $endpoint) {
        echo "<h3>Test de l'endpoint: $endpoint</h3>";
        
        // Initialiser cURL
        $ch = curl_init($apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Exécuter la requête
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Afficher les résultats
        echo "<p>Code HTTP: <strong>$httpCode</strong></p>";
        
        if ($error) {
            echo "<p>Erreur cURL: <strong>$error</strong></p>";
        }
        
        echo "<p>Réponse brute:</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Analyser la réponse JSON si possible
        if ($response) {
            $jsonResponse = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p>Réponse décodée:</p>";
                echo "<ul>";
                foreach ($jsonResponse as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        echo "<li><strong>$key</strong>: " . htmlspecialchars(json_encode($value)) . "</li>";
                    } else {
                        echo "<li><strong>$key</strong>: " . htmlspecialchars($value) . "</li>";
                    }
                }
                echo "</ul>";
                
                // Vérifier si la licence est valide
                if (isset($jsonResponse['valid'])) {
                    echo "<p>Statut de la licence: <strong>" . 
                        ($jsonResponse['valid'] ? "Valide ✅" : "Non valide ❌") . 
                        "</strong></p>";
                }
            } else {
                echo "<p>La réponse n'est pas un JSON valide.</p>";
            }
        }
        
        echo "<hr>";
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
        hr { margin: 30px 0; border: 0; border-top: 1px solid #ddd; }
        form { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test des clés API - AdminLicence</h1>
        
        <form method="post" action="">
            <h2>Configuration du test</h2>
            
            <label for="apiKey">Clé API:</label>
            <input type="text" id="apiKey" name="apiKey" value="<?php echo htmlspecialchars($apiKey); ?>" required>
            
            <label for="apiSecret">Secret API (optionnel):</label>
            <input type="text" id="apiSecret" name="apiSecret" value="<?php echo htmlspecialchars($apiSecret); ?>">
            
            <label for="serialKey">Clé de licence à vérifier:</label>
            <input type="text" id="serialKey" name="serialKey" value="<?php echo htmlspecialchars($serialKey); ?>" required>
            
            <label for="domain">Domaine:</label>
            <input type="text" id="domain" name="domain" value="<?php echo htmlspecialchars($domain); ?>" required>
            
            <label for="ipAddress">Adresse IP:</label>
            <input type="text" id="ipAddress" name="ipAddress" value="<?php echo htmlspecialchars($ipAddress); ?>" required>
            
            <button type="submit" name="test">Tester l'API</button>
        </form>
        
        <?php
        // Exécuter le test si le formulaire est soumis
        if (isset($_POST['test'])) {
            // Récupérer les valeurs du formulaire
            $apiKey = $_POST['apiKey'] ?? $apiKey;
            $apiSecret = $_POST['apiSecret'] ?? $apiSecret;
            $serialKey = $_POST['serialKey'] ?? $serialKey;
            $domain = $_POST['domain'] ?? $domain;
            $ipAddress = $_POST['ipAddress'] ?? $ipAddress;
            
            // Exécuter le test
            testLicenseVerification($apiUrl, $apiKey, $apiSecret, $serialKey, $domain, $ipAddress);
        }
        ?>
    </div>
</body>
</html>
