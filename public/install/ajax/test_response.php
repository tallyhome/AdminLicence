<?php
/**
 * Script de test pour vérifier si la réponse JSON est correctement formée
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

// Créer une réponse JSON de test
$response = [
    'status' => true,
    'message' => 'Test réussi',
    'steps' => [
        [
            'step' => 'test_step',
            'status' => true,
            'message' => 'Étape de test réussie',
            'output' => ['Ligne de sortie 1', 'Ligne de sortie 2']
        ]
    ],
    'project_type' => 'laravel'
];

// Renvoyer la réponse
echo json_encode($response);
