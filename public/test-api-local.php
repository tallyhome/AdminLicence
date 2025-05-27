<?php
/*
 * Script de test pour vérifier l'API locale AdminLicence v1.8.0
 * Ce script interroge directement la base de données locale
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->boot();

use App\Models\SerialKey;
use Illuminate\Support\Carbon;

// Fonction pour formater correctement une date
function formatDate($date) {
    if (!$date) return null;
    return Carbon::parse($date)->format('d/m/Y');
}

// Récupérer toutes les clés avec différents statuts
$keys = [
    'active' => SerialKey::where('status', 'active')->first(),
    'suspended' => SerialKey::where('status', 'suspended')->first(),
    'revoked' => SerialKey::where('status', 'revoked')->first()
];

// Afficher les informations sur les clés trouvées
echo "=================================================================\n";
echo "  VÉRIFICATION DES INFORMATIONS DES CLÉS DE LICENCE (LOCAL)\n";
echo "=================================================================\n\n";

foreach ($keys as $status => $key) {
    echo "-------------------------------------------------------------\n";
    echo "STATUT: " . strtoupper($status) . "\n";
    echo "-------------------------------------------------------------\n";
    
    if (!$key) {
        echo "Aucune clé avec le statut '$status' trouvée dans la base de données.\n\n";
        continue;
    }
    
    // Afficher les informations de la clé
    echo "Clé de série: " . $key->serial_key . "\n";
    echo "Statut: " . $key->status . "\n";
    echo "Date d'expiration (brute): " . $key->expires_at . "\n";
    echo "Date d'expiration (formatée): " . formatDate($key->expires_at) . "\n";
    echo "Projet: " . ($key->project ? $key->project->name : 'N/A') . "\n";
    echo "Domaine: " . ($key->domain ?: 'Non défini') . "\n";
    echo "Adresse IP: " . ($key->ip_address ?: 'Non définie') . "\n\n";
    
    // Simuler la réponse de l'API
    $apiResponse = [
        'status' => $key->status == 'active' ? 'success' : 'error',
        'message' => $key->status == 'active' ? 'Clé de série valide' : 'Clé de série ' . $key->status,
        'data' => [
            'token' => md5($key->serial_key . ($key->domain ?: '') . ($key->ip_address ?: '') . time()),
            'project' => $key->project ? $key->project->name : 'AdminLicence',
            'expires_at' => formatDate($key->expires_at)
        ]
    ];
    
    // Afficher la réponse simulée
    echo "RÉPONSE API SIMULÉE:\n";
    echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}

// Créer une clé expirée simulée pour tester
echo "-------------------------------------------------------------\n";
echo "STATUT: EXPIRÉ (SIMULÉ)\n";
echo "-------------------------------------------------------------\n";

$expiredKey = clone ($keys['active'] ?? new SerialKey());
if ($expiredKey->id) {
    $expiredKey->expires_at = '2023-01-01T00:00:00.000000Z';
    
    echo "Clé de série: " . $expiredKey->serial_key . "\n";
    echo "Statut: " . $expiredKey->status . " (mais expirée)\n";
    echo "Date d'expiration (brute): " . $expiredKey->expires_at . "\n";
    echo "Date d'expiration (formatée): " . formatDate($expiredKey->expires_at) . "\n\n";
    
    // Simuler la réponse de l'API pour une clé expirée
    $apiResponse = [
        'status' => 'error',
        'message' => 'Clé de série expirée',
        'data' => [
            'token' => md5($expiredKey->serial_key . ($expiredKey->domain ?: '') . ($expiredKey->ip_address ?: '') . time()),
            'project' => $expiredKey->project ? $expiredKey->project->name : 'AdminLicence',
            'expires_at' => formatDate($expiredKey->expires_at)
        ]
    ];
    
    echo "RÉPONSE API SIMULÉE:\n";
    echo json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "Pas de clé active disponible pour simuler une clé expirée.\n";
}

echo "\n=================================================================\n";
echo "  RECOMMENDATIONS POUR L'IMPLÉMENTATION\n";
echo "=================================================================\n";
echo "1. Vérifier que les routes API sont correctement configurées dans routes/api.php\n";
echo "2. Modifier LicenceService.php pour inclure le statut (suspendu, révoqué, expiré)\n";
echo "3. Formater la date d'expiration au format jj/mm/aaaa\n";
echo "4. Tester avec une clé active, suspendue, révoquée et expirée\n";
echo "=================================================================\n";
