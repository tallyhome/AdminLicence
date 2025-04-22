<?php
/**
 * Installateur optimisé pour AdminLicence
 * Version: 1.0.0
 * Date: 2025-04-15
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions/language.php';
require_once __DIR__ . '/functions/core.php';
require_once __DIR__ . '/functions/database.php';
require_once __DIR__ . '/functions/installation.php';
require_once __DIR__ . '/functions/ui.php';

// Fonction pour capturer les erreurs fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        writeToLog("ERREUR FATALE: {$error['message']} dans {$error['file']} à la ligne {$error['line']}", 'FATAL');
    }
});

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialiser la langue
$currentLang = initLanguage();

// Vérifier si Laravel est déjà installé
if (isLaravelInstalled() && !isset($_GET['force'])) {
    showError(t('already_installed') . ' <a href="install_new.php?force=1" style="color: #007bff;">Forcer la réinstallation</a>');
    exit;
}

// Gérer les étapes d'installation
$step = isset($_POST['step']) ? (int)$_POST['step'] : (isset($_GET['step']) ? (int)$_GET['step'] : 1);
$errors = [];

try {
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        switch ($step) {
            case 1: // Vérification de la licence
                if (empty($_POST['serial_key'])) {
                    $errors[] = t('license_key_required');
                } else {
                    $licenseCheck = verifierLicence($_POST['serial_key']);
                    if (!$licenseCheck['valide']) {
                        $errors[] = $licenseCheck['message'];
                    } else {
                        $_SESSION['license_data'] = $licenseCheck['donnees'];
                        $step = 2;
                    }
                }
                break;
                
            case 2: // Configuration de la base de données
                $requiredFields = ['db_host', 'db_port', 'db_name', 'db_user'];
                $missingFields = [];
                
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        $missingFields[] = $field;
                    }
                }
                
                if (!empty($missingFields)) {
                    $errors[] = t('required_fields_missing');
                } else {
                    // Tester la connexion à la base de données
                    try {
                        $dsn = "mysql:host={$_POST['db_host']};port={$_POST['db_port']}";
                        $pdo = new PDO($dsn, $_POST['db_user'], $_POST['db_password'], [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_TIMEOUT => 5
                        ]);
                        
                        // Vérifier si la base de données existe
                        $stmt = $pdo->query("SHOW DATABASES LIKE '{$_POST['db_name']}'");
                        if ($stmt->rowCount() === 0) {
                            // Essayer de créer la base de données
                            try {
                                $pdo->exec("CREATE DATABASE `{$_POST['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                            } catch (PDOException $e) {
                                $errors[] = t('database_creation_failed');
                                writeToLog("Erreur lors de la création de la base de données: " . $e->getMessage(), 'ERROR');
                                break;
                            }
                        }
                        
                        // Stocker les informations de connexion en session
                        $_SESSION['db_config'] = [
                            'host' => $_POST['db_host'],
                            'port' => $_POST['db_port'],
                            'database' => $_POST['db_name'],
                            'username' => $_POST['db_user'],
                            'password' => $_POST['db_password']
                        ];
                        
                        $step = 3;
                    } catch (PDOException $e) {
                        $errors[] = t('database_connection_failed');
                        writeToLog("Erreur de connexion à la base de données: " . $e->getMessage(), 'ERROR');
                    }
                }
                break;
                
            case 3: // Configuration du compte admin
                $requiredFields = ['admin_name', 'admin_email', 'admin_password', 'admin_password_confirm'];
                $missingFields = [];
                
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field])) {
                        $missingFields[] = $field;
                    }
                }
                
                if (!empty($missingFields)) {
                    $errors[] = t('required_fields_missing');
                } elseif ($_POST['admin_password'] !== $_POST['admin_password_confirm']) {
                    $errors[] = t('password_mismatch');
                } elseif (!filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = t('invalid_email');
                } else {
                    // Stocker les informations de l'administrateur en session
                    $_SESSION['admin_config'] = [
                        'name' => $_POST['admin_name'],
                        'email' => $_POST['admin_email'],
                        'password' => $_POST['admin_password']
                    ];
                    
                    $step = 4;
                }
                break;
                
            case 4: // Installation finale
                try {
                    // Créer le fichier .env à partir du modèle
                    if (!createEnvFile()) {
                        $errors[] = t('env_creation_failed');
                        break;
                    }
                    
                    // Exécuter les migrations et créer l'administrateur
                    if (!runMigrations()) {
                        $errors[] = t('migrations_failed');
                        break;
                    }
                    
                    if (!createAdminUser()) {
                        $errors[] = t('admin_creation_failed');
                        break;
                    }
                    
                    // Marquer l'installation comme terminée
                    if (!finalizeInstallation()) {
                        $errors[] = t('installation_finalization_failed');
                        break;
                    }
                    
                    // Rediriger vers la page de succès
                    header('Location: install_new.php?success=1');
                    exit;
                } catch (Exception $e) {
                    $errors[] = t('installation_error');
                    writeToLog("Erreur lors de l'installation finale: " . $e->getMessage(), 'ERROR');
                }
                break;
        }
    }
    
    // Afficher la page de succès si l'installation est terminée
    if (isset($_GET['success']) && $_GET['success'] == 1) {
        displaySuccessPage();
        exit;
    }
} catch (Exception $e) {
    writeToLog("Erreur lors du traitement du formulaire: " . $e->getMessage());
    showError(t('installation_error'), $e->getMessage());
}

// Afficher le formulaire d'installation
displayInstallationForm($step, $errors);
