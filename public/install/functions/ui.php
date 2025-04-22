<?php
/**
 * Fonctions d'interface utilisateur pour l'installateur
 */

/**
 * Affiche une erreur de manière sécurisée
 * 
 * @param string $message Message d'erreur
 * @param string|null $details Détails de l'erreur
 * @return void
 */
function showError($message, $details = null) {
    $lang = getCurrentLanguage();
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="' . $lang . '">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . t('installation_error') . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
            .container { max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1 { color: #d9534f; }
            .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
            .details { background: #f8f9fa; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-family: monospace; }
            .btn { display: inline-block; background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
            .btn:hover { background: #2980b9; }
            .language-selector { text-align: right; margin-bottom: 20px; }
            .language-selector a { margin-left: 10px; text-decoration: none; }
            .language-selector a.active { font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="language-selector">
                ' . getLanguageLinks() . '
            </div>
            <h1>' . t('installation_error') . '</h1>
            <div class="error">' . htmlspecialchars($message) . '</div>';
    
    if ($details) {
        echo '<div class="details">' . htmlspecialchars($details) . '</div>';
    }
    
    echo '<a href="index.php" class="btn">' . t('retry') . '</a>
        </div>
    </body>
    </html>';
    exit;
}

/**
 * Affiche le formulaire d'installation
 * 
 * @param int $step Étape actuelle
 * @param array $errors Erreurs à afficher
 * @return void
 */
function displayInstallationForm($step, $errors = []) {
    $currentLang = getCurrentLanguage();
    
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="' . $currentLang . '">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . t('installation_title') . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; background: #f4f4f4; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1 { color: #2c3e50; margin-bottom: 30px; text-align: center; }
            .step { text-align: center; margin-bottom: 30px; }
            .step span { display: inline-block; width: 30px; height: 30px; border-radius: 50%; background: #ddd; color: #666; line-height: 30px; margin: 0 5px; }
            .step span.active { background: #3498db; color: white; }
            .step span.completed { background: #2ecc71; color: white; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; color: #666; }
            input[type="text"], input[type="password"], input[type="email"], select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
            .btn { display: inline-block; background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
            .btn:hover { background: #2980b9; }
            .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
            .language-selector { text-align: right; margin-bottom: 20px; }
            .language-selector a { margin-left: 10px; text-decoration: none; color: #3498db; }
            .language-selector a.active { font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="language-selector">
                ' . getLanguageLinks() . '
            </div>
            <h1>' . t('installation_title') . '</h1>
            
            <div class="step">
                <span class="' . ($step >= 1 ? 'active' : '') . '">1</span>
                <span class="' . ($step >= 2 ? 'active' : '') . '">2</span>
                <span class="' . ($step >= 3 ? 'active' : '') . '">3</span>
                <span class="' . ($step >= 4 ? 'active' : '') . '">4</span>
            </div>';
            
    // Afficher les erreurs s'il y en a
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="error">' . htmlspecialchars($error) . '</div>';
        }
    }
    
    // Afficher le formulaire en fonction de l'étape
    switch ($step) {
        case 1: // Sélection de la langue et vérification de la licence
            echo '<form method="post" action="index.php">
                <input type="hidden" name="step" value="1">
                
                <div class="form-group">
                    <label for="serial_key">' . t('license_key') . '</label>
                    <input type="text" id="serial_key" name="serial_key" required 
                           placeholder="XXXX-XXXX-XXXX-XXXX" 
                           pattern="[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}" 
                           value="' . htmlspecialchars($_POST['serial_key'] ?? '') . '">
                </div>
                
                <div style="text-align: right;">
                    <button type="submit" class="btn">' . t('next') . '</button>
                </div>
            </form>';
            break;
            
        case 2: // Configuration de la base de données
            echo '<form method="post" action="index.php">
                <input type="hidden" name="step" value="2">
                
                <div class="form-group">
                    <label for="db_host">' . t('database_host') . '</label>
                    <input type="text" id="db_host" name="db_host" required 
                           value="' . htmlspecialchars($_POST['db_host'] ?? 'localhost') . '">
                </div>
                
                <div class="form-group">
                    <label for="db_port">' . t('database_port') . '</label>
                    <input type="text" id="db_port" name="db_port" required 
                           value="' . htmlspecialchars($_POST['db_port'] ?? '3306') . '">
                </div>
                
                <div class="form-group">
                    <label for="db_name">' . t('database_name') . '</label>
                    <input type="text" id="db_name" name="db_name" required 
                           value="' . htmlspecialchars($_POST['db_name'] ?? '') . '">
                </div>
                
                <div class="form-group">
                    <label for="db_user">' . t('database_username') . '</label>
                    <input type="text" id="db_user" name="db_user" required 
                           value="' . htmlspecialchars($_POST['db_user'] ?? '') . '">
                </div>
                
                <div class="form-group">
                    <label for="db_password">' . t('database_password') . '</label>
                    <input type="password" id="db_password" name="db_password" 
                           value="' . htmlspecialchars($_POST['db_password'] ?? '') . '">
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <a href="index.php" class="btn">' . t('back') . '</a>
                    <button type="submit" class="btn">' . t('next') . '</button>
                </div>
            </form>';
            break;
            
        case 3: // Configuration du compte admin
            echo '<form method="post" action="index.php">
                <input type="hidden" name="step" value="3">
                
                <div class="form-group">
                    <label for="admin_name">' . t('admin_name') . '</label>
                    <input type="text" id="admin_name" name="admin_name" required 
                           value="' . htmlspecialchars($_POST['admin_name'] ?? '') . '">
                </div>
                
                <div class="form-group">
                    <label for="admin_email">' . t('admin_email') . '</label>
                    <input type="email" id="admin_email" name="admin_email" required 
                           value="' . htmlspecialchars($_POST['admin_email'] ?? '') . '">
                </div>
                
                <div class="form-group">
                    <label for="admin_password">' . t('admin_password') . '</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password_confirm">' . t('admin_password_confirm') . '</label>
                    <input type="password" id="admin_password_confirm" name="admin_password_confirm" required>
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <a href="index.php?step=2" class="btn">' . t('back') . '</a>
                    <button type="submit" class="btn">' . t('next') . '</button>
                </div>
            </form>';
            break;
            
        case 4: // Installation finale
            echo '<form method="post" action="index.php">
                <input type="hidden" name="step" value="4">
                
                <div style="margin-bottom: 20px;">
                    <h3>' . t('installation_summary') . '</h3>
                    <p>' . t('installation_summary_text') . '</p>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                        <h4>' . t('database_information') . '</h4>
                        <p><strong>' . t('database_host') . ':</strong> ' . htmlspecialchars($_SESSION['db_config']['host'] ?? '') . '</p>
                        <p><strong>' . t('database_name') . ':</strong> ' . htmlspecialchars($_SESSION['db_config']['database'] ?? '') . '</p>
                        <p><strong>' . t('database_username') . ':</strong> ' . htmlspecialchars($_SESSION['db_config']['username'] ?? '') . '</p>
                        
                        <h4>' . t('admin_information') . '</h4>
                        <p><strong>' . t('admin_name') . ':</strong> ' . htmlspecialchars($_SESSION['admin_config']['name'] ?? '') . '</p>
                        <p><strong>' . t('admin_email') . ':</strong> ' . htmlspecialchars($_SESSION['admin_config']['email'] ?? '') . '</p>
                    </div>
                    
                    <p>' . t('installation_warning') . '</p>
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <a href="index.php?step=3" class="btn">' . t('back') . '</a>
                    <button type="submit" class="btn">' . t('install_now') . '</button>
                </div>
            </form>';
            break;
    }
    
    echo '</div>
    </body>
    </html>';
}

/**
 * Affiche la page de succès après l'installation
 * 
 * @return void
 */
function displaySuccessPage() {
    $currentLang = getCurrentLanguage();
    
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="' . $currentLang . '">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . t('installation_complete') . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; background: #f4f4f4; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1 { color: #2c3e50; margin-bottom: 30px; text-align: center; }
            .success-icon { text-align: center; margin-bottom: 30px; font-size: 80px; color: #2ecc71; }
            .btn { display: inline-block; background: #3498db; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
            .btn:hover { background: #2980b9; }
            .info { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>' . t('installation_complete') . '</h1>
            <div class="success-icon">✓</div>
            <div class="info">
                <p>' . t('installation_success_message') . '</p>
                <p>' . t('admin_credentials_reminder') . '</p>
            </div>
            <div style="text-align: center;">
                <a href="' . dirname(dirname($_SERVER['REQUEST_URI'])) . '/admin/login" class="btn">' . t('go_to_admin') . '</a>
                <a href="' . dirname(dirname($_SERVER['REQUEST_URI'])) . '" class="btn">' . t('go_to_homepage') . '</a>
            </div>
        </div>
    </body>
    </html>';
}
