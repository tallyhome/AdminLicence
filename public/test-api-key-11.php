<?php
/**
 * Fonction pour vérifier une licence via l'API AdminLicence
 * 
 * @param string $serialKey Clé de licence à vérifier
 * @param string $apiUrl URL de l'API de vérification
 * @param string $domain Domaine du site (optionnel)
 * @param string $ipAddress Adresse IP du serveur (optionnel)
 * @return array Résultat de la vérification
 */
function verifierLicence($serialKey, $apiUrl, $domain = null, $ipAddress = null) {
    // Données à envoyer
    $data = [
        'serial_key' => $serialKey,
        'domain' => $domain ?: (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
        'ip_address' => $ipAddress ?: (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '')
    ];
    
    // Initialiser cURL
    $ch = curl_init($apiUrl);
    
    // Configurer cURL
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true // Activé pour la sécurité en production
    ]);
    
    // Exécuter la requête
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    // Fermer la session cURL
    curl_close($ch);
    
    // Traiter la réponse
    if ($error) {
        return [
            'valid' => false,
            'message' => "Erreur de connexion: $error",
            'data' => null
        ];
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'valid' => false,
            'message' => "Erreur de décodage JSON: " . json_last_error_msg(),
            'data' => null
        ];
    }
    
    // Préparer le résultat final
    $isValid = ($httpCode == 200 && isset($result['status']) && $result['status'] == 'success');
    $licenceData = $result['data'] ?? null;
    
    return [
        'valid' => $isValid,
        'message' => $result['message'] ?? 'Erreur inconnue',
        'data' => $licenceData,
        // Informations supplémentaires extraites pour faciliter l'utilisation
        'is_expired' => $licenceData['is_expired'] ?? false,
        'is_suspended' => $licenceData['is_suspended'] ?? false,
        'is_revoked' => $licenceData['is_revoked'] ?? false,
        'expires_at' => $licenceData['expires_at'] ?? null,
        'project' => $licenceData['project'] ?? null,
        'token' => $licenceData['token'] ?? null,
        'status' => $licenceData['status'] ?? null
    ];
}

// Exemple d'utilisation
$licenceKey = 'VOTRE-CLE-DE-LICENCE'; // Remplacez par votre clé de licence
$apiUrl = 'https://licence.votredomaine.com/api/check-serial.php'; // URL de l'API

$resultat = verifierLicence($licenceKey, $apiUrl);

if ($resultat['valid']) {
    echo "Licence valide !\n";
    echo "Projet: " . $resultat['project'] . "\n";
    echo "Expire le: " . $resultat['expires_at'] . "\n";
    
    // Vérification des statuts spécifiques
    if ($resultat['is_expired']) {
        echo "ATTENTION: Cette licence a expiré.\n";
    }
    
    if ($resultat['is_suspended']) {
        echo "ATTENTION: Cette licence est suspendue.\n";
    }
    
    if ($resultat['is_revoked']) {
        echo "ATTENTION: Cette licence a été révoquée.\n";
    }
} else {
    echo "Licence invalide: " . $resultat['message'] . "\n";
}