<?php
// Point d'entrée API v1 pour la vérification des licences
// Version simplifiée et robuste pour éviter les erreurs d'index indéfini

try {
    // Activer la journalisation des erreurs
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../../../storage/logs/api-errors.log');
    
    // Désactiver l'affichage des erreurs
    ini_set('display_errors', 0);
    
    // Charger l'application Laravel
    require_once __DIR__ . '/../../../vendor/autoload.php';
    $app = require_once __DIR__ . '/../../../bootstrap/app.php';
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
        isset($data['domain']) ? $data['domain'] : null,
        isset($data['ip_address']) ? $data['ip_address'] : null
    );
    
    // Envoi de la réponse
    header('Content-Type: application/json');
    
    if (!isset($result['valid']) || $result['valid'] !== true) {
        echo json_encode([
            'status' => 'error',
            'message' => isset($result['message']) ? $result['message'] : 'Licence invalide',
        ]);
    } else {
        // Générer un token manuellement
        $token = md5($data['serial_key'] . time() . rand(1000, 9999));
        
        // Formater la date d'expiration au format jj/mm/aaaa si nécessaire
        $expiryDate = isset($result['expires_at']) ? $result['expires_at'] : null;
        if ($expiryDate && !preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $expiryDate)) {
            try {
                $date = new \DateTime($expiryDate);
                $expiryDate = $date->format('d/m/Y');
            } catch (\Exception $e) {
                // Garder la date telle quelle si le format n'est pas reconnu
                error_log('Erreur de formatage de date: ' . $e->getMessage());
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Clé de série valide',
            'data' => [
                'token' => $token,
                'project' => isset($result['project']) ? $result['project'] : '',
                'expires_at' => $expiryDate,
                'status' => isset($result['status']) ? $result['status'] : 'active',
                'is_expired' => isset($result['is_expired']) ? $result['is_expired'] : false,
                'is_suspended' => isset($result['is_suspended']) ? $result['is_suspended'] : false,
                'is_revoked' => isset($result['is_revoked']) ? $result['is_revoked'] : false
            ],
        ]);
    }
    
    // Terminer la requête
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    // Journaliser l'erreur
    error_log('API Error: ' . $e->getMessage());
    
    // Renvoyer une réponse d'erreur générique
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Une erreur s\'est produite lors de la vérification de la licence.',
    ]);
}
