<?php
/**
 * Redirection vers le wizard d'installation personnalisé
 * 
 * Ce script redirige vers le wizard d'installation personnalisé
 * situé dans le dossier /install.
 */

// Désactiver l'affichage des erreurs pour la production
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Définir le fuseau horaire par défaut
date_default_timezone_set('UTC');

// Déterminer le chemin d'installation
$installPath = __DIR__ . '/install/index.php?step=1';

// Vérifier si le fichier d'installation existe
if (!file_exists(__DIR__ . '/' . $installPath)) {
    // Afficher une erreur si le fichier n'existe pas
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur d\'installation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
            .container { max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1 { color: #d9534f; }
            .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
            .btn { display: inline-block; background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Erreur d\'installation</h1>
            <div class="error">Le fichier d\'installation n\'a pas été trouvé. Veuillez vérifier que tous les fichiers ont été correctement téléchargés.</div>
            <a href="/" class="btn">Retour à l\'accueil</a>
        </div>
    </body>
    </html>';
    exit;
}

// Rediriger vers le wizard d'installation personnalisé
header('Location: ' . $installPath);
exit;