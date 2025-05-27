<?php
// Point d'entrée API direct pour la vérification des licences
require_once __DIR__ . '/../../vendor/autoload.php';

// Initialisation de l'application Laravel
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Traitement de la requête
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Vérification que la requête est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Méthode non autorisée. Utilisez POST.',
    ]);
    exit;
}

// Récupération des données de la requête
$data = json_decode(file_get_contents('php://input'), true) ?: [];

// Validation des données
if (empty($data['serial_key'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Le paramètre serial_key est requis.',
    ]);
    exit;
}

// Récupération des services nécessaires
$licenceService = $app->make(\App\Services\LicenceService::class);

// Validation de la clé de série
$result = $licenceService->validateSerialKey(
    $data['serial_key'],
    $data['domain'] ?? null,
    $data['ip_address'] ?? null
);

// Envoi de la réponse
header('Content-Type: application/json');
if (!$result['valid']) {
    echo json_encode([
        'status' => 'error',
        'message' => $result['message'],
    ]);
} else {
    // Générer un token si nécessaire
    $token = $result['token'] ?? md5($data['serial_key'] . time() . rand(1000, 9999));
    
    // Formater la date d'expiration au format jj/mm/aaaa si nécessaire
    $expiryDate = $result['expires_at'] ?? null;
    if ($expiryDate && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $expiryDate)) {
        try {
            $date = new \DateTime($expiryDate);
            $expiryDate = $date->format('d/m/Y');
        } catch (\Exception $e) {
            // Garder la date telle quelle si le format n'est pas reconnu
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Clé de série valide',
        'data' => [
            'token' => $token,
            'project' => $result['project'] ?? '',
            'expires_at' => $expiryDate,
            'status' => $result['status'] ?? 'active',
            'is_expired' => $result['is_expired'] ?? false,
            'is_suspended' => $result['is_suspended'] ?? false,
            'is_revoked' => $result['is_revoked'] ?? false
        ],
    ]);
}

// Terminer la requête
$kernel->terminate($request, $response);
