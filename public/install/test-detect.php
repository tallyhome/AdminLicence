<?php
/**
 * Script de test pour la détection de projet
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir le type de contenu comme HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de détection de projet</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Test de détection de projet</h1>
    
    <div id="test-result" class="result">Cliquez sur le bouton pour tester la détection de projet...</div>
    
    <button id="test-button">Tester la détection</button>
    
    <h2>Résultat brut :</h2>
    <pre id="raw-result">Aucun résultat pour le moment</pre>
    
    <script>
    $(document).ready(function() {
        $('#test-button').click(function() {
            $('#test-result').removeClass('success error').text('Test en cours...');
            
            // Afficher l'URL complète pour débogage
            var ajaxUrl = 'ajax/detect_project.php';
            console.log('URL AJAX:', ajaxUrl);
            
            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                data: {
                    test: 1
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Réponse:', response);
                    $('#test-result').addClass('success').text('Test réussi !');
                    $('#raw-result').text(JSON.stringify(response, null, 2));
                },
                error: function(xhr, status, error) {
                    console.error('Erreur:', error);
                    console.error('Status:', status);
                    console.error('Réponse:', xhr.responseText);
                    
                    $('#test-result').addClass('error').text('Erreur: ' + error);
                    $('#raw-result').text('Status: ' + status + '\nErreur: ' + error + '\nRéponse: ' + xhr.responseText);
                }
            });
        });
    });
    </script>
</body>
</html>
