<?php
// Enregistrer ce fichier comme test-api-endpoints.php et l'exécuter avec "php test-api-endpoints.php"

// Configuration
$baseUrl = 'http://127.0.0.1:8000'; // Remplacez par votre URL si différente
$serialKey = 'VOTRE-CLE-DE-LICENCE'; // Remplacez par une clé de licence valide
$domain = 'exemple.com'; // Domaine de test
$ipAddress = '127.0.0.1'; // IP de test

// Données à envoyer
$data = [
    'serial_key' => $serialKey,
    'domain' => $domain,
    'ip_address' => $ipAddress
];

// Points d'entrée à tester
$endpoints = [
    'Point d\'entrée PHP direct' => '/api/check-serial.php',
    'Point d\'entrée PHP v1' => '/api/v1/check-serial.php',
    'Route Laravel' => '/api/check-serial',
    'Route Laravel v1' => '/api/v1/check-serial',
];

echo "=== Test des points d'entrée API pour les clés de licence ===\n\n";

foreach ($endpoints as $name => $endpoint) {
    echo "Test de $name ($endpoint):\n";
    
    // Initialisation de cURL
    $ch = curl_init($baseUrl . $endpoint);
    
    // Configuration de cURL
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    // Exécution de la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    // Fermeture de cURL
    curl_close($ch);
    
    // Affichage des résultats
    if ($error) {
        echo "  Erreur: $error\n";
    } else {
        echo "  Code HTTP: $httpCode\n";
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "  Erreur de décodage JSON: " . json_last_error_msg() . "\n";
            echo "  Réponse brute: " . substr($response, 0, 100) . "...\n";
        } else {
            echo "  Statut: " . ($result['status'] ?? 'non défini') . "\n";
            echo "  Message: " . ($result['message'] ?? 'non défini') . "\n";
            
            if (isset($result['data'])) {
                echo "  Données:\n";
                echo "    - Projet: " . ($result['data']['project'] ?? 'non défini') . "\n";
                echo "    - Date d'expiration: " . ($result['data']['expires_at'] ?? 'non définie') . "\n";
                echo "    - Statut: " . ($result['data']['status'] ?? 'non défini') . "\n";
                echo "    - Expiré: " . ($result['data']['is_expired'] ? 'Oui' : 'Non') . "\n";
                echo "    - Suspendu: " . ($result['data']['is_suspended'] ? 'Oui' : 'Non') . "\n";
                echo "    - Révoqué: " . ($result['data']['is_revoked'] ? 'Oui' : 'Non') . "\n";
            }
        }
    }
    
    echo "\n";
}

echo "=== Fin des tests ===\n";