<?php
/**
 * Fichier de test complet pour l'API de vérification de licence
 * 
 * Ce script permet de tester l'API de vérification de licence
 * en envoyant une requête POST avec une clé de licence.
 * 
 * Il affiche les résultats de manière claire et détaillée.
 */

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuration - Modifiez ces valeurs selon vos besoins
$cleTest = "ZNWQ-E0GP-QYVH-BPHZ"; // Remplacez par votre clé de licence à tester
$domaine = $_SERVER['SERVER_NAME'] ?? "example.com";
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? "127.0.0.1";

// URLs à tester
$urlsAPI = [
    "API ultra-simple" => "http://127.0.0.1:8000/api/ultra-simple.php",
    "API simple" => "http://127.0.0.1:8000/api/simple-check.php",
    "API standard" => "http://127.0.0.1:8000/api/check-serial.php",
    "API v1" => "http://127.0.0.1:8000/api/v1/check-serial.php"
];

/**
 * Fonction pour vérifier une licence
 * 
 * @param string $url URL de l'API
 * @param string $cleSeriale Clé de licence à vérifier
 * @param string $domaine Domaine du site
 * @param string $adresseIP Adresse IP du serveur
 * @return array Résultat de la vérification
 */
function verifierLicence($url, $cleSeriale, $domaine, $adresseIP) {
    // Données à envoyer
    $donnees = [
        'serial_key' => $cleSeriale,
        'domain' => $domaine,
        'ip_address' => $adresseIP
    ];
    
    // Initialiser cURL
    $ch = curl_init($url);
    
    // Configurer cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($donnees));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 secondes
    
    // Exécuter la requête
    $reponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erreur = curl_error($ch);
    $info = curl_getinfo($ch);
    
    // Fermer la session cURL
    curl_close($ch);
    
    // Décoder la réponse JSON
    $resultatJSON = json_decode($reponse, true);
    
    // Préparer le résultat
    return [
        'url' => $url,
        'donnees' => $donnees,
        'http_code' => $httpCode,
        'temps_total' => isset($info['total_time']) ? round($info['total_time'] * 1000) . ' ms' : 'N/A',
        'reponse_brute' => $reponse,
        'reponse' => $resultatJSON,
        'erreur_curl' => $erreur ?: null,
        'valide' => ($httpCode == 200 && isset($resultatJSON['status']) && $resultatJSON['status'] === 'success'),
        'message' => isset($resultatJSON['message']) ? $resultatJSON['message'] : 'Erreur inconnue'
    ];
}

// Tester chaque URL d'API
$resultats = [];
foreach ($urlsAPI as $nom => $url) {
    $resultats[$nom] = verifierLicence($url, $cleTest, $domaine, $ipAddress);
}

// Fonction pour générer un exemple de code d'intégration
function genererExempleCode($url) {
    $code = '<?php
/**
 * Fonction pour vérifier une licence
 * 
 * @param string $cleSeriale Clé de licence à vérifier
 * @param string $domaine Domaine du site (optionnel)
 * @param string $adresseIP Adresse IP du serveur (optionnel)
 * @return array Résultat de la vérification
 */
function verifierLicence($cleSeriale, $domaine = null, $adresseIP = null) {
    // URL de l\'API de vérification
    $url = "' . $url . '";
    
    // Données à envoyer
    $donnees = [
        \'serial_key\' => $cleSeriale,
        \'domain\' => $domaine ?: $_SERVER[\'SERVER_NAME\'],
        \'ip_address\' => $adresseIP ?: $_SERVER[\'REMOTE_ADDR\']
    ];
    
    // Initialiser cURL
    $ch = curl_init($url);
    
    // Configurer cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($donnees));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        \'Content-Type: application/json\'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 secondes
    
    // Exécuter la requête
    $reponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Décoder la réponse JSON
    $resultat = json_decode($reponse, true);
    
    // Préparer le résultat
    return [
        \'valide\' => ($httpCode == 200 && isset($resultat[\'status\']) && $resultat[\'status\'] === \'success\'),
        \'message\' => $resultat[\'message\'] ?? \'Erreur inconnue\',
        \'donnees\' => $resultat[\'data\'] ?? null
    ];
}

// Exemple d\'utilisation
$resultat = verifierLicence(\'VOTRE-CLE-DE-LICENCE\');

if ($resultat[\'valide\']) {
    // La licence est valide, activer les fonctionnalités
    echo "Licence valide! Vous pouvez utiliser l\'application.";
    
    // Vous pouvez accéder aux données supplémentaires
    $token = $resultat[\'donnees\'][\'token\'] ?? \'\';
    $projet = $resultat[\'donnees\'][\'project\'] ?? \'\';
    $expiration = $resultat[\'donnees\'][\'expires_at\'] ?? null;
} else {
    // La licence est invalide, limiter les fonctionnalités
    echo "Erreur de licence: " . $resultat[\'message\'];
}';

    return $code;
}

// Trouver l'API qui fonctionne le mieux
$apiRecommandee = null;
foreach ($resultats as $nom => $resultat) {
    if ($resultat['valide']) {
        $apiRecommandee = $nom;
        break;
    }
}
if (!$apiRecommandee) {
    // Si aucune API ne fonctionne, prendre celle avec le code HTTP le plus proche de 200
    $meilleurCode = 999;
    foreach ($resultats as $nom => $resultat) {
        if ($resultat['http_code'] < $meilleurCode) {
            $meilleurCode = $resultat['http_code'];
            $apiRecommandee = $nom;
        }
    }
}

// URL recommandée pour l'intégration
$urlRecommandee = $urlsAPI[$apiRecommandee];

// Générer l'exemple de code
$exempleCode = genererExempleCode($urlRecommandee);

// Afficher la page HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test API Licence AdminLicence</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-top: 0;
        }
        h2 {
            color: #3498db;
            margin-top: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        h3 {
            color: #2c3e50;
            margin-top: 20px;
        }
        pre {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            overflow: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            line-height: 1.5;
        }
        .success {
            color: #27ae60;
            font-weight: bold;
        }
        .error {
            color: #e74c3c;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
        .info {
            background-color: #f1f8ff;
            border-left: 4px solid #3498db;
            padding: 10px 15px;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .api-result {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        .api-result.success {
            border-left: 4px solid #27ae60;
        }
        .api-result.error {
            border-left: 4px solid #e74c3c;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
        }
        .badge-success {
            background-color: #27ae60;
            color: white;
        }
        .badge-error {
            background-color: #e74c3c;
            color: white;
        }
        .badge-warning {
            background-color: #f39c12;
            color: white;
        }
        .code-container {
            position: relative;
        }
        .copy-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 12px;
        }
        .copy-btn:hover {
            background-color: #2980b9;
        }
        .recommended {
            background-color: #e8f7f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test API Licence AdminLicence</h1>
        
        <div class="info">
            <p>Ce script teste les différentes API de vérification de licence avec la clé <strong><?php echo htmlspecialchars($cleTest); ?></strong>.</p>
            <p>Domaine de test: <strong><?php echo htmlspecialchars($domaine); ?></strong></p>
            <p>Adresse IP de test: <strong><?php echo htmlspecialchars($ipAddress); ?></strong></p>
        </div>
        
        <h2>Résultats des tests</h2>
        
        <table>
            <tr>
                <th>API</th>
                <th>URL</th>
                <th>Code HTTP</th>
                <th>Temps</th>
                <th>Statut</th>
                <th>Message</th>
            </tr>
            <?php foreach ($resultats as $nom => $resultat): ?>
            <tr class="<?php echo $nom === $apiRecommandee ? 'recommended' : ''; ?>">
                <td><?php echo htmlspecialchars($nom); ?> <?php echo $nom === $apiRecommandee ? '<span class="badge badge-success">Recommandée</span>' : ''; ?></td>
                <td><?php echo htmlspecialchars($resultat['url']); ?></td>
                <td><?php echo $resultat['http_code']; ?></td>
                <td><?php echo $resultat['temps_total']; ?></td>
                <td>
                    <?php if ($resultat['valide']): ?>
                    <span class="success">Valide</span>
                    <?php else: ?>
                    <span class="error">Erreur</span>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($resultat['message']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <?php foreach ($resultats as $nom => $resultat): ?>
        <div class="api-result <?php echo $resultat['valide'] ? 'success' : 'error'; ?>">
            <h3><?php echo htmlspecialchars($nom); ?> <?php echo $nom === $apiRecommandee ? '<span class="badge badge-success">Recommandée</span>' : ''; ?></h3>
            
            <h4>Détails de la requête</h4>
            <pre>URL: <?php echo htmlspecialchars($resultat['url']); ?>

Données envoyées:
<?php echo htmlspecialchars(json_encode($resultat['donnees'], JSON_PRETTY_PRINT)); ?>

Méthode: POST
Content-Type: application/json</pre>
            
            <h4>Réponse</h4>
            <pre>Code HTTP: <?php echo $resultat['http_code']; ?>
Temps de réponse: <?php echo $resultat['temps_total']; ?>

<?php if ($resultat['erreur_curl']): ?>
Erreur cURL: <?php echo htmlspecialchars($resultat['erreur_curl']); ?>
<?php endif; ?>

<?php if ($resultat['reponse_brute']): ?>
<?php echo htmlspecialchars(json_encode($resultat['reponse'], JSON_PRETTY_PRINT) ?: $resultat['reponse_brute']); ?>
<?php else: ?>
Aucune réponse reçue de l'API.
<?php endif; ?></pre>
            
            <?php if ($resultat['valide']): ?>
            <p class="success">✅ La licence est valide!</p>
            <?php else: ?>
            <p class="error">❌ La licence n'est pas valide: <?php echo htmlspecialchars($resultat['message']); ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <h2>Code d'intégration recommandé</h2>
        <p>Utilisez ce code pour intégrer la vérification de licence dans votre application. Nous recommandons d'utiliser l'API <strong><?php echo htmlspecialchars($apiRecommandee); ?></strong> car elle a donné les meilleurs résultats lors des tests.</p>
        
        <div class="code-container">
            <button class="copy-btn" onclick="copyCode()">Copier le code</button>
            <pre id="code-to-copy"><?php echo htmlspecialchars($exempleCode); ?></pre>
        </div>
        
        <h2>Comment utiliser ce code</h2>
        <ol>
            <li>Copiez le code ci-dessus dans votre application.</li>
            <li>Remplacez <code>VOTRE-CLE-DE-LICENCE</code> par la clé de licence de votre client.</li>
            <li>Appelez la fonction <code>verifierLicence()</code> pour vérifier si la licence est valide.</li>
            <li>Utilisez le résultat pour activer ou désactiver les fonctionnalités de votre application.</li>
        </ol>
        
        <div class="info">
            <p>Pour plus d'informations sur l'API de vérification de licence, consultez la documentation ou contactez l'administrateur du système.</p>
        </div>
    </div>
    
    <script>
    function copyCode() {
        const codeElement = document.getElementById('code-to-copy');
        const textArea = document.createElement('textarea');
        textArea.value = codeElement.textContent;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        const button = document.querySelector('.copy-btn');
        button.textContent = 'Copié!';
        setTimeout(() => {
            button.textContent = 'Copier le code';
        }, 2000);
    }
    </script>
</body>
</html>