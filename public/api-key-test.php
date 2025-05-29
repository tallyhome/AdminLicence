<?php
/**
 * Script de test direct pour les clés API d'AdminLicence
 */

// Charger l'environnement Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Accéder aux services Laravel
$apiKeyService = $app->make('App\Services\ApiKeyService');

// Fonction pour afficher les informations d'une clé API
function displayApiKeyInfo($apiKey) {
    echo "<h3>Informations sur la clé API</h3>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $apiKey->id . "</li>";
    echo "<li><strong>Nom:</strong> " . htmlspecialchars($apiKey->name) . "</li>";
    echo "<li><strong>Clé:</strong> " . htmlspecialchars($apiKey->key) . "</li>";
    echo "<li><strong>Projet:</strong> " . htmlspecialchars($apiKey->project->name) . "</li>";
    echo "<li><strong>Statut:</strong> " . ($apiKey->revoked_at ? 'Révoquée' : ($apiKey->expires_at && $apiKey->expires_at->isPast() ? 'Expirée' : 'Active')) . "</li>";
    echo "<li><strong>Permissions:</strong> " . implode(', ', $apiKey->permissions ?? []) . "</li>";
    echo "<li><strong>Dernière utilisation:</strong> " . ($apiKey->last_used_at ? $apiKey->last_used_at->format('Y-m-d H:i:s') : 'Jamais') . "</li>";
    echo "<li><strong>Créée le:</strong> " . $apiKey->created_at->format('Y-m-d H:i:s') . "</li>";
    echo "<li><strong>Expire le:</strong> " . ($apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : 'Jamais') . "</li>";
    echo "</ul>";
}

// Fonction pour tester une clé API
function testApiKey($apiKeyService, $key, $secret = null) {
    echo "<h3>Test de validation de la clé API</h3>";
    
    if (empty($secret)) {
        echo "<p class='warning'>Attention: Le secret n'est pas fourni, donc le test de validation complet ne peut pas être effectué.</p>";
        
        // Vérifier si la clé existe
        $apiKey = \App\Models\ApiKey::where('key', $key)->first();
        if ($apiKey) {
            echo "<p class='success'>✅ La clé API existe dans la base de données.</p>";
            
            // Vérifier si la clé est active
            if ($apiKey->revoked_at) {
                echo "<p class='error'>❌ La clé API est révoquée.</p>";
            } elseif ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
                echo "<p class='error'>❌ La clé API est expirée.</p>";
            } else {
                echo "<p class='success'>✅ La clé API est active.</p>";
            }
            
            return $apiKey;
        } else {
            echo "<p class='error'>❌ La clé API n'existe pas dans la base de données.</p>";
            return null;
        }
    } else {
        // Test complet avec secret
        $isValid = $apiKeyService->validateKey($key, $secret);
        if ($isValid) {
            echo "<p class='success'>✅ La clé API est valide.</p>";
            return \App\Models\ApiKey::where('key', $key)->first();
        } else {
            echo "<p class='error'>❌ La clé API n'est pas valide.</p>";
            return null;
        }
    }
}

// Fonction pour tester l'API de vérification de licence
function testLicenseVerification($apiKey, $serialKey, $domain, $ipAddress) {
    echo "<h3>Test de l'API de vérification de licence</h3>";
    
    $data = [
        'serial_key' => $serialKey,
        'domain' => $domain,
        'ip_address' => $ipAddress
    ];
    
    $headers = [
        'X-API-KEY' => $apiKey,
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];
    
    // Créer une requête HTTP simulée
    $request = Illuminate\Http\Request::create('/api/v1/check-serial', 'POST', [], [], [], [], json_encode($data));
    
    // Ajouter les en-têtes
    foreach ($headers as $key => $value) {
        $request->headers->set($key, $value);
    }
    
    // Exécuter la requête
    $licenceApiController = $app->make('App\Http\Controllers\Api\LicenceApiController');
    
    try {
        $response = $licenceApiController->checkSerial($request);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        
        echo "<p><strong>Code de statut:</strong> " . $statusCode . "</p>";
        echo "<p><strong>Réponse:</strong></p>";
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
        
        // Analyser la réponse JSON
        $jsonResponse = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p><strong>Réponse décodée:</strong></p>";
            echo "<ul>";
            foreach ($jsonResponse as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . "</li>";
                } else {
                    echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</li>";
                }
            }
            echo "</ul>";
            
            // Vérifier si la licence est valide
            if (isset($jsonResponse['valid'])) {
                echo "<p><strong>Statut de la licence:</strong> " . 
                    ($jsonResponse['valid'] ? "<span class='success'>Valide ✅</span>" : "<span class='error'>Non valide ❌</span>") . 
                    "</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p class='error'><strong>Erreur:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// Récupérer les paramètres
$apiKey = $_GET['api_key'] ?? 'sk_Tc53GD4UycO0kMyQs9UZrBTVFKAWpIxz';
$serialKey = $_GET['serial_key'] ?? '9QXH-YDNF-WBFL-XFTU';
$domain = $_GET['domain'] ?? 'exemple.com';
$ipAddress = $_GET['ip_address'] ?? $_SERVER['REMOTE_ADDR'];
$action = $_GET['action'] ?? '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test direct des clés API - AdminLicence</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .container { max-width: 1200px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        hr { margin: 20px 0; border: 0; border-top: 1px solid #ddd; }
        .card { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px; }
        form { margin: 20px 0; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"] { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test direct des clés API - AdminLicence</h1>
        
        <div class="card">
            <h2>Configuration du test</h2>
            <form method="get" action="">
                <label for="api_key">Clé API:</label>
                <input type="text" id="api_key" name="api_key" value="<?php echo htmlspecialchars($apiKey); ?>">
                
                <label for="serial_key">Clé de licence à vérifier:</label>
                <input type="text" id="serial_key" name="serial_key" value="<?php echo htmlspecialchars($serialKey); ?>">
                
                <label for="domain">Domaine:</label>
                <input type="text" id="domain" name="domain" value="<?php echo htmlspecialchars($domain); ?>">
                
                <label for="ip_address">Adresse IP:</label>
                <input type="text" id="ip_address" name="ip_address" value="<?php echo htmlspecialchars($ipAddress); ?>">
                
                <input type="hidden" name="action" value="test">
                
                <button type="submit">Tester la clé API</button>
            </form>
        </div>
        
        <?php if ($action === 'test'): ?>
            <div class="card">
                <h2>Résultats du test</h2>
                
                <?php
                // Tester la clé API
                $apiKeyObj = testApiKey($apiKeyService, $apiKey);
                
                if ($apiKeyObj) {
                    // Afficher les informations de la clé API
                    displayApiKeyInfo($apiKeyObj);
                    
                    // Tester l'API de vérification de licence
                    testLicenseVerification($apiKey, $serialKey, $domain, $ipAddress);
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
