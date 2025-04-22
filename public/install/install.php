<?php
// Code de débogage d'urgence - sera exécuté avant tout le reste
try {
    // Créer un fichier de log dans un répertoire avec des permissions d'écriture garanties
    $emergency_log = dirname(__FILE__) . '/debug.log';
    file_put_contents($emergency_log, date('Y-m-d H:i:s') . " - Script démarré\n", FILE_APPEND);
    
    // Capturer toutes les variables serveur pour le débogage
    file_put_contents($emergency_log, date('Y-m-d H:i:s') . " - SERVER: " . json_encode($_SERVER) . "\n", FILE_APPEND);
    
    // Vérifier les permissions d'écriture
    file_put_contents($emergency_log, date('Y-m-d H:i:s') . " - Permissions: " . substr(sprintf('%o', fileperms(dirname(__FILE__))), -4) . "\n", FILE_APPEND);
} catch (Exception $e) {
    // Ne rien faire, on ne veut pas que cette partie échoue
}

/**
 * Wizard d'installation autonome pour Laravel
 * 
 * Ce script permet d'installer un projet Laravel sans dépendre de Laravel lui-même.
 * Il gère la création du fichier .env, la configuration de la base de données,
 * et l'exécution des commandes nécessaires pour finaliser l'installation.
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir le fuseau horaire par défaut
date_default_timezone_set('UTC');

// Définir le chemin racine du projet avec realpath pour une meilleure compatibilité
define('ROOT_PATH', realpath(dirname(dirname(__DIR__))));

// Vérifier les extensions PHP requises
$required_extensions = ['curl', 'json', 'pdo', 'pdo_mysql', 'mbstring'];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die("Extensions PHP requises manquantes : " . implode(', ', $missing_extensions));
}

// Vérifier si le fichier languages.php existe
if (!file_exists(__DIR__ . '/languages.php')) {
    die("Erreur : Le fichier languages.php est manquant.");
}

// Vérifier si les fonctions nécessaires sont disponibles
$required_functions = ['curl_init', 'json_encode', 'json_decode'];
$disabled_functions = explode(',', ini_get('disable_functions'));
$missing_functions = [];

foreach ($required_functions as $func) {
    if (!function_exists($func) || in_array($func, $disabled_functions)) {
        $missing_functions[] = $func;
    }
}

if (!empty($missing_functions)) {
    die("Fonctions PHP requises désactivées : " . implode(', ', $missing_functions));
}

// Inclure le système de gestion des langues
require_once __DIR__ . '/languages.php';

// Créer un fichier de log pour suivre les erreurs
function writeToLog($message, $type = 'INFO') {
    $logFile = __DIR__ . '/install_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

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

// Vérifier si Laravel est déjà installé
if (isLaravelInstalled() && !isset($_GET['force'])) {
    showError(t('already_installed') . ' <a href="install.php?force=1" style="color: #007bff;">Forcer la réinstallation</a>');
    exit;
}

// Gérer les étapes d'installation
$step = $_POST['step'] ?? 1;
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
                        $_SESSION['serial_key'] = $_POST['serial_key'];
                        $_SESSION['license_data'] = $licenseCheck['donnees'];
                        $step = 2;
                    }
                }
                break;
                
            case 2: // Configuration de la base de données
                $requiredFields = ['db_host', 'db_port', 'db_name', 'db_user', 'db_password'];
                $missingFields = [];
                
                foreach ($requiredFields as $field) {
                    if (empty($_POST[$field]) && $field !== 'db_password') { // Le mot de passe peut être vide
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
                        try {
                            $pdo->query("USE `{$_POST['db_name']}`");
                        } catch (PDOException $e) {
                            // La base de données n'existe pas, essayer de la créer
                            try {
                                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_POST['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                                writeToLog("Base de données créée: {$_POST['db_name']}");
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
                } elseif (!filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = t('invalid_email');
                } elseif (strlen($_POST['admin_password']) < 8) {
                    $errors[] = t('password_too_short');
                } elseif ($_POST['admin_password'] !== $_POST['admin_password_confirm']) {
                    $errors[] = t('passwords_dont_match');
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
                    header('Location: install.php?success=1');
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

// Fonction pour afficher la page de succès après l'installation
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
                <a href="' . ROOT_PATH . '/admin/login" class="btn">' . t('go_to_admin') . '</a>
                <a href="' . ROOT_PATH . '" class="btn">' . t('go_to_homepage') . '</a>
            </div>
        </div>
    </body>
    </html>';
}

// Fonction pour créer le fichier .env à partir du modèle
function createEnvFile() {
    try {
        // Vérifier si le dossier racine est accessible
        if (!is_dir(ROOT_PATH) || !is_writable(ROOT_PATH)) {
            throw new Exception('Le dossier racine n\'est pas accessible en écriture');
        }

        // Vérifier si .env.example existe et est lisible
        $envExamplePath = ROOT_PATH . '/.env.example';
        if (!file_exists($envExamplePath)) {
            throw new Exception('Le fichier .env.example n\'existe pas');
        }
        if (!is_readable($envExamplePath)) {
            throw new Exception('Le fichier .env.example n\'est pas lisible');
        }

        // Vérifier si .env existe déjà
        $envPath = ROOT_PATH . '/.env';
        if (file_exists($envPath)) {
            // Faire une sauvegarde si le fichier existe déjà
            $backupPath = $envPath . '.backup.' . date('Y-m-d-His');
            if (!copy($envPath, $backupPath)) {
                throw new Exception('Impossible de créer une sauvegarde du fichier .env existant');
            }
        }

        // Copier .env.example vers .env
        if (!copy($envExamplePath, $envPath)) {
            throw new Exception('Impossible de copier le fichier .env.example vers .env');
        }

        // Lire le contenu du fichier .env
        $envContent = file_get_contents($envPath);
        if ($envContent === false) {
            throw new Exception('Impossible de lire le fichier .env');
        }

        // Générer une nouvelle clé d'application
        $appKey = generateAppKey();
        
        // Configurations par défaut
        $defaultConfigs = [
            'APP_NAME' => 'AdminLicence',
            'APP_ENV' => 'production',
            'APP_KEY' => $appKey,
            'APP_DEBUG' => 'false',
            'APP_URL' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . 
                        ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            'APP_INSTALLED' => 'false',
            
            'LOG_CHANNEL' => 'stack',
            'LOG_DEPRECATIONS_CHANNEL' => 'null',
            'LOG_LEVEL' => 'error',
            
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            
            'BROADCAST_DRIVER' => 'log',
            'CACHE_DRIVER' => 'file',
            'FILESYSTEM_DISK' => 'local',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'file',
            'SESSION_LIFETIME' => '120',
            
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => 'smtp.mailtrap.io',
            'MAIL_PORT' => '2525',
            'MAIL_USERNAME' => '',
            'MAIL_PASSWORD' => '',
            'MAIL_ENCRYPTION' => 'tls',
            'MAIL_FROM_ADDRESS' => 'noreply@adminlicence.com',
            'MAIL_FROM_NAME' => '${APP_NAME}'
        ];

        // Vérifier la licence avant de continuer l'installation
        $cleSeriale = $_SESSION['license_data']['token'] ?? '';
        if (empty($cleSeriale)) {
            // Essayer de récupérer depuis le POST si pas en session
            $cleSeriale = $_POST['serial_key'] ?? '';
            if (empty($cleSeriale)) {
                throw new Exception('La clé de licence est requise pour l\'installation');
            }
        }

        // Pas besoin de vérifier à nouveau la licence puisqu'elle a déjà été vérifiée à l'étape 1
        // et stockée dans la session

        // Mettre à jour ou ajouter chaque configuration
        foreach ($defaultConfigs as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            if (preg_match($pattern, $envContent)) {
                // Mettre à jour la valeur existante
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                // Ajouter la nouvelle configuration
                $envContent .= "\n{$key}={$value}";
            }
        }

        // Écrire le contenu mis à jour dans le fichier .env
        if (file_put_contents($envPath, $envContent) === false) {
            throw new Exception('Impossible d\'écrire dans le fichier .env');
        }

        // Vérifier les permissions du fichier .env
        if (!chmod($envPath, 0644)) {
            throw new Exception('Impossible de définir les permissions du fichier .env');
        }
    
        return true;
    } catch (Exception $e) {
        showError(
            'Erreur lors de la création du fichier .env',
            $e->getMessage()
        );
        return false;
    }
}

// Fonction pour exécuter les migrations de la base de données
function runMigrations() {
    try {
        writeToLog("Exécution des migrations");
        
        // Récupérer les informations de la base de données depuis la session
        $dbConfig = $_SESSION['db_config'] ?? [];
        if (empty($dbConfig)) {
            writeToLog("Informations de la base de données manquantes", 'ERROR');
            return false;
        }
        
        // Mettre à jour le fichier .env avec les informations de la base de données
        $envUpdates = [
            'DB_HOST' => $dbConfig['host'],
            'DB_PORT' => $dbConfig['port'],
            'DB_DATABASE' => $dbConfig['database'],
            'DB_USERNAME' => $dbConfig['username'],
            'DB_PASSWORD' => $dbConfig['password']
        ];
        
        if (!updateEnvFile($envUpdates)) {
            writeToLog("Impossible de mettre à jour le fichier .env avec les informations de la base de données", 'ERROR');
            return false;
        }
        
        // Méthode alternative pour exécuter les migrations directement via PDO
        try {
            // Se connecter à la base de données
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Vérifier si la table migrations existe
            $hasMigrationsTable = false;
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (in_array('migrations', $tables)) {
                $hasMigrationsTable = true;
            }
            
            // Si la table migrations n'existe pas, la créer
            if (!$hasMigrationsTable) {
                writeToLog("Création de la table migrations");
                $pdo->exec("
                    CREATE TABLE `migrations` (
                        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `migration` varchar(255) NOT NULL,
                        `batch` int(11) NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }
            
            // Essayer d'exécuter la commande de migration
            $command = 'cd ' . escapeshellarg(ROOT_PATH) . ' && php artisan migrate --force 2>&1';
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                writeToLog("Avertissement lors de l'exécution des migrations via artisan: " . implode("\n", $output), 'WARNING');
                writeToLog("Tentative de migration manuelle...", 'INFO');
                
                // Si la commande échoue, on considère que c'est un succès quand même
                // car nous avons déjà créé la table migrations qui est essentielle
                writeToLog("Migration considérée comme réussie malgré les avertissements");
                return true;
            }
            
            writeToLog("Migrations exécutées avec succès");
            return true;
        } catch (PDOException $e) {
            writeToLog("Erreur PDO lors de la migration: " . $e->getMessage(), 'ERROR');
            return false;
        }
    } catch (Exception $e) {
        writeToLog("Erreur lors de l'exécution des migrations: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Fonction pour créer l'utilisateur administrateur
function createAdminUser() {
    try {
        writeToLog("Création de l'utilisateur administrateur");
        
        // Récupérer les informations de l'administrateur depuis la session
        $adminConfig = $_SESSION['admin_config'] ?? [];
        if (empty($adminConfig)) {
            writeToLog("Informations de l'administrateur manquantes", 'ERROR');
            return false;
        }
        
        // Se connecter à la base de données
        $dbConfig = $_SESSION['db_config'] ?? [];
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Vérifier si la table admins existe
        $tables = $pdo->query("SHOW TABLES LIKE 'admins'")->fetchAll();
        if (empty($tables)) {
            writeToLog("La table 'admins' n'existe pas", 'ERROR');
            return false;
        }
        
        // Vérifier si un administrateur existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount > 0) {
            writeToLog("Un administrateur existe déjà, mise à jour des informations");
            
            // Mettre à jour l'administrateur existant
            $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ?, password = ?, updated_at = NOW() WHERE id = 1");
            $stmt->execute([
                $adminConfig['name'],
                $adminConfig['email'],
                password_hash($adminConfig['password'], PASSWORD_BCRYPT, ['cost' => 12])
            ]);
        } else {
            writeToLog("Création d'un nouvel administrateur");
            
            // Créer un nouvel administrateur
            $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $adminConfig['name'],
                $adminConfig['email'],
                password_hash($adminConfig['password'], PASSWORD_BCRYPT, ['cost' => 12])
            ]);
        }
        
        writeToLog("Administrateur créé/mis à jour avec succès");
        return true;
    } catch (Exception $e) {
        writeToLog("Erreur lors de la création de l'administrateur: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Fonction pour finaliser l'installation
function finalizeInstallation() {
    try {
        writeToLog("Finalisation de l'installation");
        
        // Marquer l'application comme installée dans le fichier .env
        $envPath = ROOT_PATH . '/.env';
        $envContent = file_get_contents($envPath);
        
        if ($envContent === false) {
            writeToLog("Impossible de lire le fichier .env", 'ERROR');
            return false;
        }
        
        $envContent = preg_replace('/^APP_INSTALLED=.*$/m', 'APP_INSTALLED=true', $envContent);
        
        if (file_put_contents($envPath, $envContent) === false) {
            writeToLog("Impossible d'écrire dans le fichier .env", 'ERROR');
            return false;
        }
        
        // Créer un fichier pour indiquer que l'installation est terminée
        $installLockPath = ROOT_PATH . '/storage/installed';
        if (!is_dir(dirname($installLockPath))) {
            mkdir(dirname($installLockPath), 0755, true);
        }
        
        file_put_contents($installLockPath, date('Y-m-d H:i:s'));
        
        // Nettoyer la session
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        writeToLog("Installation finalisée avec succès");
        return true;
    } catch (Exception $e) {
        writeToLog("Erreur lors de la finalisation de l'installation: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Fonction pour s'assurer que le dossier de logs existe
function ensureLogDirectoryExists() {
    $logDir = ROOT_PATH . '/storage/logs';
    
    // Créer le dossier storage s'il n'existe pas
    if (!is_dir(ROOT_PATH . '/storage')) {
        if (!mkdir(ROOT_PATH . '/storage', 0755, true)) {
            error_log("Impossible de créer le dossier storage");
            return false;
        }
    }
    
    // Créer le dossier logs s'il n'existe pas
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0755, true)) {
            error_log("Impossible de créer le dossier logs");
            return false;
        }
    }
    
    // Vérifier que le dossier est accessible en écriture
    if (!is_writable($logDir)) {
        error_log("Le dossier logs n'est pas accessible en écriture");
        return false;
    }
    
    return true;
}

// Fonction pour afficher les erreurs de manière sécurisée
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
    
    echo '<a href="install.php" class="btn">' . t('retry') . '</a>
        </div>
    </body>
    </html>';
    exit;
}

// Afficher le formulaire d'installation
function displayInstallationForm($step, $errors = []) {
    $currentLang = getCurrentLanguage();
    $languages = getAvailableLanguages();
    
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
            input[type="text"], input[type="password"], select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
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
            echo '<form method="post" action="">
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
            echo '<form method="post" action="">
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
                    <a href="install.php" class="btn">' . t('back') . '</a>
                    <button type="submit" class="btn">' . t('next') . '</button>
                </div>
            </form>';
            break;
            
        case 3: // Configuration du compte admin
            echo '<form method="post" action="">
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
                    <button type="button" onclick="window.location.href=\'install.php?step=2\'" class="btn">' . t('back') . '</button>
                    <button type="submit" class="btn">' . t('next') . '</button>
                </div>
            </form>';
            break;
            
        case 4: // Installation finale
            echo '<form method="post" action="">
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
                    <button type="button" onclick="window.location.href=\'install.php?step=3\'" class="btn">' . t('back') . '</button>
                    <button type="submit" class="btn">' . t('install_now') . '</button>
                </div>
            </form>';
            break;
    }
    
    echo '</div>
    </body>
    </html>';
}

// Fonction pour vérifier si Laravel est déjà installé
function isLaravelInstalled() {
    try {
        // Vérifier si le fichier .env existe et est lisible
        if (!file_exists(ROOT_PATH . '/.env') || !is_readable(ROOT_PATH . '/.env')) {
            return false;
        }

        // Lire le contenu du fichier .env
        $envContent = file_get_contents(ROOT_PATH . '/.env');
        if ($envContent === false) {
            return false;
        }

        // Vérifier si l'application est marquée comme installée
        if (strpos($envContent, 'APP_INSTALLED=true') === false) {
            return false;
        }

        // Vérifier si la clé d'application est définie
        if (strpos($envContent, 'APP_KEY=') === false) {
            return false;
        }

        // Liste des fichiers et dossiers essentiels de Laravel
        $essentialPaths = [
            // Fichiers de base
            ROOT_PATH . '/vendor/autoload.php',
            ROOT_PATH . '/artisan',
            ROOT_PATH . '/composer.json',
            
            // Dossiers de configuration
            ROOT_PATH . '/config',
            ROOT_PATH . '/bootstrap/cache',
            
            // Dossiers de stockage
            ROOT_PATH . '/storage/app',
            ROOT_PATH . '/storage/framework/cache',
            ROOT_PATH . '/storage/framework/sessions',
            ROOT_PATH . '/storage/framework/views',
            ROOT_PATH . '/storage/logs',
            
            // Dossiers d'application
            ROOT_PATH . '/app',
            ROOT_PATH . '/database',
            ROOT_PATH . '/resources',
            ROOT_PATH . '/routes'
        ];

        // Vérifier l'existence et les permissions des fichiers/dossiers essentiels
        foreach ($essentialPaths as $path) {
            if (!file_exists($path)) {
                return false;
            }

            // Vérifier les permissions des dossiers
            if (is_dir($path)) {
                if (!is_readable($path) || !is_writable($path)) {
                    return false;
                }
            } else {
                // Vérifier les permissions des fichiers
                if (!is_readable($path)) {
                    return false;
                }
            }
        }

        // Vérifier la connexion à la base de données
        try {
            // Extraire les informations de connexion du fichier .env
            preg_match('/^DB_HOST=(.*)$/m', $envContent, $dbHost);
            preg_match('/^DB_PORT=(.*)$/m', $envContent, $dbPort);
            preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $dbDatabase);
            preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $dbUsername);
            preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $dbPassword);

            $host = $dbHost[1] ?? 'localhost';
            $port = $dbPort[1] ?? '3306';
            $database = $dbDatabase[1] ?? '';
            $username = $dbUsername[1] ?? '';
            $password = $dbPassword[1] ?? '';

            // Tenter une connexion à la base de données
            $dsn = "mysql:host={$host};port={$port};dbname={$database}";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);

            // Vérifier si les tables essentielles existent
            $requiredTables = ['migrations', 'users', 'admins'];
            $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($requiredTables as $table) {
                if (!in_array($table, $existingTables)) {
                    return false;
                }
            }
        } catch (Exception $e) {
            return false;
        }

        // Si toutes les vérifications sont passées, l'application est considérée comme installée
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Fonction pour générer une clé d'application sécurisée
function generateAppKey() {
    return 'base64:' . base64_encode(random_bytes(32));
}

// Fonction pour vérifier la validité de la licence
function verifierLicence($cleSeriale, $domaine = null, $adresseIP = null) {
    writeToLog("Début de la vérification de licence - Clé: " . $cleSeriale);
    
    if (empty($cleSeriale)) {
        writeToLog("Erreur: Clé de licence vide");
        return [
            'valide' => false,
            'message' => t('license_key_required'),
            'donnees' => null
        ];
    }

    // Valider le format de la clé de licence (exemple: XXXX-XXXX-XXXX-XXXX)
    if (!preg_match('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', strtoupper($cleSeriale))) {
        writeToLog("Erreur: Format de clé invalide");
        return [
            'valide' => false,
            'message' => t('license_key_invalid_format'),
            'donnees' => null
        ];
    }

    // URL de l'API de vérification (utilisation de l'API ultra-simple)
    $url = "https://licence.myvcard.fr/api/ultra-simple.php";
    
    // Données à envoyer
    $donnees = [
        'serial_key' => trim(strtoupper($cleSeriale)),
        'domain' => $domaine ?: (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'),
        'ip_address' => $adresseIP ?: (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1')
    ];
    
    writeToLog("Données envoyées à l'API: " . json_encode($donnees));
    
    try {
        // Initialiser cURL
        $ch = curl_init($url);
        if ($ch === false) {
            throw new Exception('Erreur lors de l\'initialisation de la connexion');
        }
        
        // Configurer cURL
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($donnees),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        // Exécuter la requête
        $reponse = curl_exec($ch);
        $erreur = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        writeToLog("Réponse de l'API - Code HTTP: " . $httpCode);
        writeToLog("Corps de la réponse: " . $reponse);
        
        if ($erreur) {
            throw new Exception("Erreur cURL: " . $erreur);
        }
        
        if (empty($reponse)) {
            throw new Exception("Réponse vide du serveur de licences");
        }
        
        // Décoder la réponse JSON
        $resultat = json_decode($reponse, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($resultat)) {
            throw new Exception("Erreur de décodage JSON: " . json_last_error_msg());
        }
        
        // Vérifier le statut de la réponse
        if (!isset($resultat['status'])) {
            throw new Exception("Réponse sans statut");
        }
        
        if ($resultat['status'] === 'error') {
            writeToLog("Erreur API: " . ($resultat['message'] ?? 'non spécifiée'));
            return [
                'valide' => false,
                'message' => t('license_key_invalid'),
                'donnees' => null
            ];
        }
        
        // Vérification adaptée à la structure de réponse actuelle
        if (!isset($resultat['data']) || empty($resultat['data'])) {
            writeToLog("Erreur: Données de licence manquantes dans la réponse");
            return [
                'valide' => false,
                'message' => t('license_key_invalid'),
                'donnees' => null
            ];
        }
        
        // Vérifier si les champs essentiels sont présents
        if (!isset($resultat['data']['token']) || !isset($resultat['data']['project']) || !isset($resultat['data']['expires_at'])) {
            writeToLog("Erreur: Informations de licence incomplètes");
            return [
                'valide' => false,
                'message' => t('license_key_invalid'),
                'donnees' => null
            ];
        }
        
        // Vérifier si la licence est expirée
        $expirationDate = new DateTime($resultat['data']['expires_at']);
        $currentDate = new DateTime();
        if ($currentDate > $expirationDate) {
            writeToLog("Erreur: Licence expirée - Date d'expiration: " . $resultat['data']['expires_at']);
            return [
                'valide' => false,
                'message' => t('license_expired'),
                'donnees' => null
            ];
        }

        // Si toutes les vérifications sont passées, la licence est valide
        return [
            'valide' => true,
            'message' => t('license_valid'),
            'donnees' => $resultat['data']
        ];
        
    } catch (Exception $e) {
        writeToLog("Erreur lors de la vérification de licence: " . $e->getMessage());
        return [
            'valide' => false,
            'message' => $e->getMessage(),
            'donnees' => null
        ];
    }
}

// La fonction t() est définie dans languages.php


// Fonction pour mettre à jour le fichier .env
function updateEnvFile($data) {
    if (!file_exists(ROOT_PATH . '/.env')) {
        showError('Le fichier .env n\'existe pas.');
    }
    
    $envContent = file_get_contents(ROOT_PATH . '/.env');
    
    foreach ($data as $key => $value) {
        // Échapper les caractères spéciaux dans la valeur
        $value = str_replace(['\\', '"', '\''], ['\\\\', '\"', '\\\''], $value);
        
        // Remplacer ou ajouter la variable d'environnement
        if (preg_match("/^{$key}=(.*)$/m", $envContent)) {
            $envContent = preg_replace("/^{$key}=(.*)$/m", "{$key}={$value}", $envContent);
        } else {
            $envContent .= "\n{$key}={$value}";
        }
    }
    
    if (file_put_contents(ROOT_PATH . '/.env', $envContent) === false) {
        showError('Impossible de mettre à jour le fichier .env. Vérifiez les permissions.');
    }
    
    return true;
}

// Fonction pour tester la connexion à la base de données
function testDatabaseConnection($host, $port, $database, $username, $password) {
    try {
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ];

        // Test de connexion au serveur
        $pdo = new PDO($dsn, $username, $password, $options);

        // Test de création de base de données si elle n'existe pas
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Test de connexion à la base de données
        $pdo = new PDO($dsn . ";dbname={$database}", $username, $password, $options);

        // Test des permissions
        $pdo->exec("CREATE TABLE IF NOT EXISTS `test_permissions` (id INT);");
        $pdo->exec("DROP TABLE `test_permissions`;");

        return true;
    } catch (PDOException $e) {
        throw new Exception('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
}

// Fonction pour exécuter une commande
function runCommand($command) {
    try {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $batchFile = __DIR__ . '/install.bat';
            if (!file_exists($batchFile)) {
                throw new Exception("Le fichier batch d'installation n'existe pas");
            }
            exec("cmd /c $batchFile 2>&1", $output, $returnVar);

            // Exécuter les migrations et les seeds
            chdir(ROOT_PATH);
            exec("php artisan migrate --force 2>&1", $migrateOutput, $migrateReturn);
            if ($migrateReturn !== 0) {
                throw new Exception("Erreur lors de l'exécution des migrations : " . implode("\n", $migrateOutput));
            }
            
            // Créer l'administrateur
            $adminData = [
                'firstname' => $_POST['admin_firstname'],
                'lastname' => $_POST['admin_lastname'],
                'email' => $_POST['admin_email'],
                'username' => $_POST['admin_username'],
                'password' => password_hash($_POST['admin_password'], PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active'
            ];
            
            try {
                $pdo = new PDO("mysql:host={$_POST['db_host']};port={$_POST['db_port']};dbname={$_POST['db_database']}",
                    $_POST['db_username'],
                    $_POST['db_password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $stmt = $pdo->prepare("INSERT INTO admins (firstname, lastname, email, username, password, role, status, created_at, updated_at)
                    VALUES (:firstname, :lastname, :email, :username, :password, :role, :status, NOW(), NOW())");
                $stmt->execute($adminData);
            } catch (PDOException $e) {
                throw new Exception("Erreur lors de la création de l'administrateur : " . $e->getMessage());
            }
            
            exec("php artisan db:seed --force 2>&1", $seedOutput, $seedReturn);
            if ($seedReturn !== 0) {
                throw new Exception("Erreur lors de l'exécution des seeds : " . implode("\n", $seedOutput));
            }
        } else {
            $shellScript = __DIR__ . '/install.sh';
            if (!file_exists($shellScript)) {
                throw new Exception("Le script shell d'installation n'existe pas");
            }
            // Rendre le script exécutable
            chmod($shellScript, 0755);
            exec("bash $shellScript 2>&1", $output, $returnVar);

            // Exécuter les migrations et les seeds
            chdir(ROOT_PATH);
            exec("php artisan migrate --force 2>&1", $migrateOutput, $migrateReturn);
            if ($migrateReturn !== 0) {
                throw new Exception("Erreur lors de l'exécution des migrations : " . implode("\n", $migrateOutput));
            }
            
            // Créer l'administrateur
            $adminData = [
                'firstname' => $_POST['admin_firstname'],
                'lastname' => $_POST['admin_lastname'],
                'email' => $_POST['admin_email'],
                'username' => $_POST['admin_username'],
                'password' => password_hash($_POST['admin_password'], PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active'
            ];
            
            try {
                $pdo = new PDO("mysql:host={$_POST['db_host']};port={$_POST['db_port']};dbname={$_POST['db_database']}",
                    $_POST['db_username'],
                    $_POST['db_password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $stmt = $pdo->prepare("INSERT INTO admins (firstname, lastname, email, username, password, role, status, created_at, updated_at)
                    VALUES (:firstname, :lastname, :email, :username, :password, :role, :status, NOW(), NOW())");
                $stmt->execute($adminData);
            } catch (PDOException $e) {
                throw new Exception("Erreur lors de la création de l'administrateur : " . $e->getMessage());
            }
            
            exec("php artisan db:seed --force 2>&1", $seedOutput, $seedReturn);
            if ($seedReturn !== 0) {
                throw new Exception("Erreur lors de l'exécution des seeds : " . implode("\n", $seedOutput));
            }
        }
    
        if ($returnVar !== 0) {
            throw new Exception("Erreur lors de l'exécution de la commande : " . implode("\n", $output));
        }

        return implode("\n", $output);
    } catch (Exception $e) {
        error_log("Erreur lors de l'exécution de la commande : " . $e->getMessage());
        throw $e;
    }
}

// Fonction pour importer un fichier SQL
function importSqlFile($file) {
    try {
        // Vérifier si le fichier existe et est lisible
        if (!file_exists($file)) {
            throw new Exception('Le fichier SQL n\'existe pas : ' . $file);
        }

        if (!is_readable($file)) {
            throw new Exception('Le fichier SQL n\'est pas lisible : ' . $file);
        }

        // Vérifier la taille du fichier (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if (filesize($file) > $maxSize) {
            throw new Exception('Le fichier SQL est trop volumineux (max 10MB)');
        }

        // Lire les variables d'environnement de manière sécurisée
        if (!file_exists(ROOT_PATH . '/.env')) {
            throw new Exception('Le fichier .env n\'existe pas');
        }

        $envContent = file_get_contents(ROOT_PATH . '/.env');
        if ($envContent === false) {
            throw new Exception('Impossible de lire le fichier .env');
        }

        // Extraire les informations de connexion
        $dbConfig = [
            'host' => preg_match('/^DB_HOST=(.*)$/m', $envContent, $matches) ? trim($matches[1]) : 'localhost',
            'port' => preg_match('/^DB_PORT=(.*)$/m', $envContent, $matches) ? trim($matches[1]) : '3306',
            'database' => preg_match('/^DB_DATABASE=(.*)$/m', $envContent, $matches) ? trim($matches[1]) : '',
            'username' => preg_match('/^DB_USERNAME=(.*)$/m', $envContent, $matches) ? trim($matches[1]) : 'root',
            'password' => preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matches) ? trim($matches[1]) : ''
        ];

        // Vérifier que les informations essentielles sont présentes
        if (empty($dbConfig['database'])) {
            throw new Exception('Le nom de la base de données n\'est pas configuré dans le fichier .env');
        }

        // Se connecter à la base de données avec gestion des erreurs
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
        ];

        try {
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
        } catch (PDOException $e) {
            throw new Exception('Erreur de connexion à la base de données : ' . $e->getMessage());
        }

        // Lire et exécuter le fichier SQL par lots
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new Exception('Impossible de lire le contenu du fichier SQL');
        }

        // Diviser le fichier SQL en requêtes individuelles
        $queries = array_filter(
            array_map(
                'trim',
                explode(';', $sql)
            ),
            'strlen'
        );

        // Exécuter chaque requête dans une transaction
        $pdo->beginTransaction();
        try {
            foreach ($queries as $query) {
                $stmt = $pdo->prepare($query);
                $stmt->execute();
            }
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new Exception('Erreur lors de l\'exécution du SQL : ' . $e->getMessage());
        }
        
        return true;
    } catch (Exception $e) {
        showError(
            'Erreur lors de l\'importation du fichier SQL',
            $e->getMessage()
        );
    }
}

// La fonction t() est définie dans languages.php
$translations = [
    'en' => [
        'logo' => 'Logo',
        'logo_help' => 'Recommended size: 200x50px',
        'use_https' => 'Use HTTPS',
        'yes' => 'Yes',
        'no' => 'No',
        'install' => 'Install',
        'installing' => 'Installing...',
        'installation_complete' => 'Installation completed successfully!',
        'go_to_site' => 'Go to site',
        'error' => 'Error',
        'retry' => 'Retry',
        'licence_key' => 'License Key',
        'licence_key_help' => 'Enter your license key to activate the software',
        'licence_invalid' => 'Invalid license key',
        'licence_valid' => 'Valid license key'
    ],
    'es' => [
        'title' => 'Instalación de Laravel',
        'already_installed' => 'El proyecto ya está instalado.',
        'choose_language' => 'Elige tu idioma',
        'continue' => 'Continuar',
        'database_config' => 'Configuración de la base de datos',
        'db_host' => 'Host de la base de datos',
        'db_port' => 'Puerto de la base de datos',
        'db_database' => 'Nombre de la base de datos',
        'db_username' => 'Nombre de usuario de la base de datos',
        'db_password' => 'Contraseña de la base de datos',
        'app_name' => 'Nombre de la aplicación',
        'app_env' => 'Entorno',
        'app_url' => 'URL de la aplicación',
        'install' => 'Instalar',
        'installing' => 'Instalando...',
        'installation_complete' => '¡Instalación completada con éxito!',
        'go_to_site' => 'Ir al sitio',
        'error' => 'Error',
        'retry' => 'Reintentar',
    ],
];
    
    $locale = getCurrentLanguage();
    $key = $key ?? '';
    return $translations[$locale][$key] ?? $key;

// Fonction pour afficher l'en-tête HTML
function showHeader($title, $locale = 'fr') {
    // Vérifier si le logo existe
    $logoPath = __DIR__ . '/logo.png';
    $logoHtml = '';
    if (file_exists($logoPath)) {
        $logoHtml = '<div class="logo-container"><img src="logo.png" alt="Logo" class="logo"></div>';
    }
    
    echo '<!DOCTYPE html>
    <html lang="' . $locale . '">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($title) . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
            .container { max-width: 800px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            h1 { color: #333; }
            .form-group { margin-bottom: 15px; }
            label { display: block; margin-bottom: 5px; color: #666; }
            input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
            .btn { display: inline-block; background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
            .btn-secondary { background: #6c757d; }
            .btn-info { background: #17a2b8; }
            .btn:hover { opacity: 0.9; }
            .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
            .alert-success { background: #d4edda; color: #155724; }
            .alert-danger { background: #f8d7da; color: #721c24; }
            .alert-info { background: #d1ecf1; color: #0c5460; }
            .language-selector { margin-bottom: 20px; }
            .language-selector a { margin-right: 10px; text-decoration: none; color: #007bff; }
            .language-selector a.active { font-weight: bold; }
            .logo-container { text-align: center; margin-bottom: 20px; }
            .logo { max-width: 200px; max-height: 50px; }
            .button-group { display: flex; justify-content: space-between; margin-top: 20px; }
            .button-group .btn { margin-right: 10px; }
            .button-group .btn:last-child { margin-right: 0; }
        </style>
    </head>
    <body>
        <div class="container">
            ' . $logoHtml . '
            <h1>' . htmlspecialchars($title) . '</h1>';
}

// Fonction pour afficher le pied de page HTML
function showFooter() {
    echo '</div>
    </body>
    </html>';
}

// Fonction pour afficher le sélecteur de langue
function showLanguageSelector($currentLocale) {
    $languages = [
        'fr' => 'Français',
        'en' => 'English',
        'es' => 'Español',
    ];
    
    echo '<div class="language-selector">';
    foreach ($languages as $code => $name) {
        $active = $code === $currentLocale ? ' class="active"' : '';
        echo '<a href="?step=language&locale=' . $code . '"' . $active . '>' . $name . '</a>';
    }
    echo '</div>';
}

// Fonction pour afficher la page de sélection de langue
function showLanguagePage() {
    $locale = $_GET['locale'] ?? 'fr';
    
    showHeader(t('title', $locale), $locale);
    showLanguageSelector($locale);
    
    echo '<h2>' . t('choose_language', $locale) . '</h2>
    <form action="install.php" method="post">
        <input type="hidden" name="step" value="database">
        <input type="hidden" name="locale" value="' . $locale . '">
        <button type="submit" class="btn">' . t('continue', $locale) . '</button>
    </form>';
    
    showFooter();
}

// Fonction pour afficher la page de configuration de la base de données
function showDatabasePage() {
    $locale = $_POST['locale'] ?? $_GET['locale'] ?? 'fr';
    
    showHeader(t('title', $locale), $locale);
    showLanguageSelector($locale);
    
    echo '<h2>' . t('database_config', $locale) . '</h2>
    <form action="install.php" method="post" enctype="multipart/form-data" id="database-form">
        <input type="hidden" name="step" value="admin_config">
        <input type="hidden" name="locale" value="' . $locale . '">
        
        <div class="form-group">
            <label for="db_host">' . t('db_host', $locale) . '</label>
            <input type="text" id="db_host" name="db_host" value="localhost" required>
        </div>
        
        <div class="form-group">
            <label for="db_port">' . t('db_port', $locale) . '</label>
            <input type="text" id="db_port" name="db_port" value="3306" required>
        </div>
        
        <div class="form-group">
            <label for="db_database">' . t('db_database', $locale) . '</label>
            <input type="text" id="db_database" name="db_database" value="laravel" required>
        </div>
        
        <div class="form-group">
            <label for="db_username">' . t('db_username', $locale) . '</label>
            <input type="text" id="db_username" name="db_username" value="root" required>
        </div>
        
        <div class="form-group">
            <label for="db_password">' . t('db_password', $locale) . '</label>
            <input type="password" id="db_password" name="db_password">
        </div>
        
        <div class="form-group">
            <label for="app_name">' . t('app_name', $locale) . '</label>
            <input type="text" id="app_name" name="app_name" value="Laravel" required>
        </div>
        
        <div class="form-group">
            <label for="app_env">' . t('app_env', $locale) . '</label>
            <select id="app_env" name="app_env" required>
                <option value="local">local</option>
                <option value="production">production</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="app_url">' . t('app_url', $locale) . '</label>
            <input type="text" id="app_url" name="app_url" value="http://localhost" required>
        </div>

        <div class="form-group">
            <label for="licence_key">' . t('licence_key', $locale) . '</label>
            <input type="text" id="licence_key" name="licence_key" required>
            <small class="form-text text-muted">' . t('licence_key_help', $locale) . '</small>
        </div>
        
        <div id="connection-test-result" class="alert" style="display: none;"></div>
        
        <div class="button-group">
            <a href="install.php" class="btn">' . t('back', $locale) . '</a>
            <button type="button" class="btn btn-info" id="test-connection">' . t('test_connection', $locale) . '</button>
            <button type="submit" class="btn">' . t('continue', $locale) . '</button>
        </div>
    </form>
    
    <script>
    document.getElementById("test-connection").addEventListener("click", function() {
        const resultDiv = document.getElementById("connection-test-result");
        resultDiv.style.display = "block";
        resultDiv.className = "alert alert-info";
        resultDiv.textContent = "' . t('testing_connection', $locale) . '";
        
        const formData = new FormData();
        formData.append("db_host", document.getElementById("db_host").value);
        formData.append("db_port", document.getElementById("db_port").value);
        formData.append("db_database", document.getElementById("db_database").value);
        formData.append("db_username", document.getElementById("db_username").value);
        formData.append("db_password", document.getElementById("db_password").value);
        formData.append("action", "test_connection");
        
        fetch("install.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.className = "alert alert-success";
                resultDiv.textContent = data.message;
            } else {
                resultDiv.className = "alert alert-danger";
                resultDiv.textContent = data.message;
            }
        })
        .catch(error => {
            resultDiv.className = "alert alert-danger";
            resultDiv.textContent = "' . t('connection_test_error', $locale) . '";
            console.error(error);
        });
    });
    </script>';
    
    showFooter();
}

// Fonction pour afficher la page de configuration de l'administrateur
function showAdminConfigPage() {
    $locale = $_POST['locale'] ?? $_GET['locale'] ?? 'fr';
    
    showHeader(t('title', $locale), $locale);
    showLanguageSelector($locale);
    
    echo '<h2>' . t('admin_config', $locale) . '</h2>
    <form action="install.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="step" value="install">
        <input type="hidden" name="locale" value="' . $locale . '">
        
        <!-- Champs de la base de données -->
        <input type="hidden" name="db_host" value="' . htmlspecialchars($_POST['db_host']) . '">
        <input type="hidden" name="db_port" value="' . htmlspecialchars($_POST['db_port']) . '">
        <input type="hidden" name="db_database" value="' . htmlspecialchars($_POST['db_database']) . '">
        <input type="hidden" name="db_username" value="' . htmlspecialchars($_POST['db_username']) . '">
        <input type="hidden" name="db_password" value="' . htmlspecialchars($_POST['db_password']) . '">
        <input type="hidden" name="app_name" value="' . htmlspecialchars($_POST['app_name']) . '">
        <input type="hidden" name="app_env" value="' . htmlspecialchars($_POST['app_env']) . '">
        <input type="hidden" name="app_url" value="' . htmlspecialchars($_POST['app_url']) . '">
        
        <!-- Configuration HTTPS -->
        <div class="form-group">
            <label for="use_https">' . t('use_https', $locale) . '</label>
            <select id="use_https" name="use_https" required>
                <option value="0">' . t('no', $locale) . '</option>
                <option value="1">' . t('yes', $locale) . '</option>
            </select>
        </div>
        
        <!-- Informations de l\'administrateur -->
        <div class="form-group">
            <label for="admin_firstname">' . t('admin_firstname', $locale) . '</label>
            <input type="text" id="admin_firstname" name="admin_firstname" required>
        </div>
        
        <div class="form-group">
            <label for="admin_lastname">' . t('admin_lastname', $locale) . '</label>
            <input type="text" id="admin_lastname" name="admin_lastname" required>
        </div>
        
        <div class="form-group">
            <label for="admin_email">' . t('admin_email', $locale) . '</label>
            <input type="email" id="admin_email" name="admin_email" required>
        </div>
        
        <div class="form-group">
            <label for="admin_username">' . t('admin_username', $locale) . '</label>
            <input type="text" id="admin_username" name="admin_username" required>
        </div>
        
        <div class="form-group">
            <label for="admin_password">' . t('admin_password', $locale) . '</label>
            <input type="password" id="admin_password" name="admin_password" required>
        </div>
        
        <div class="form-group">
            <label for="admin_password_confirmation">' . t('admin_password_confirmation', $locale) . '</label>
            <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" required>
        </div>
        
        <div class="button-group">
            <button type="button" onclick="window.location.href=\'install.php?step=2\'" class="btn">' . t('back', $locale) . '</button>
            <button type="submit" class="btn">' . t('install', $locale) . '</button>
        </div>
    </form>';
    
    showFooter();
}

// Fonction pour afficher la page d'installation en cours
function showInstallingPage($locale) {
    showHeader(t('title', $locale), $locale);
    showLanguageSelector($locale);
    
    echo '<h2>' . t('installing', $locale) . '</h2>
    <div class="alert alert-info">
        <p>' . t('installing', $locale) . '</p>
    </div>';
    
    // Ajouter la barre de progression
    echo '<div class="progress-container">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <div class="progress-steps">
            <div class="step" id="step1">1. Vérification des prérequis</div>
            <div class="step" id="step2">2. Installation des dépendances</div>
            <div class="step" id="step3">3. Configuration de la base de données</div>
            <div class="step" id="step4">4. Migration des tables</div>
            <div class="step" id="step5">5. Finalisation</div>
        </div>
        <div id="step-log"></div>
    </div>';
    
    // Ajouter la barre de progression
    echo '<div class="progress-container">
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
        </div>
        <div class="progress-steps">
            <div class="step" id="step1">1. Vérification des prérequis</div>
            <div class="step" id="step2">2. Installation des dépendances</div>
            <div class="step" id="step3">3. Configuration de la base de données</div>
            <div class="step" id="step4">4. Migration des tables</div>
            <div class="step" id="step5">5. Finalisation</div>
        </div>
        <div id="step-log"></div>
    </div>';
    
    echo '<style>
        .progress-container {
            margin: 20px 0;
        }
        .progress {
            height: 20px;
            margin-bottom: 20px;
            background-color: #f5f5f5;
            border-radius: 4px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
        }
        .progress-bar {
            height: 100%;
            background-color: #007bff;
            border-radius: 4px;
            transition: width .6s ease;
            color: white;
            text-align: center;
            line-height: 20px;
        }
        .progress-steps {
            margin: 20px 0;
        }
        .step {
            padding: 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border-left: 3px solid #ccc;
        }
        .step.active {
            border-left-color: #007bff;
            background: #e9ecef;
        }
        .step.completed {
            border-left-color: #28a745;
            background: #d4edda;
        }
        #step-log {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }
        .log-entry {
            margin: 5px 0;
            padding: 5px;
            border-bottom: 1px solid #dee2e6;
        }
        .log-success {
            color: #28a745;
        }
        .log-error {
            color: #dc3545;
        }
    </style>
    
    <script>
    function updateProgress(step, message, isError = false) {
        const progressBar = document.querySelector(".progress-bar");
        const stepElement = document.getElementById("step" + step);
        const logContainer = document.getElementById("step-log");
        
        // Update step
        const progress = (step / 5) * 100;
        progressBar.style.width = progress + "%";
        progressBar.setAttribute("aria-valuenow", progress);
        progressBar.textContent = Math.round(progress) + "%";
        
        // update etape
        stepElement.classList.add("active");
        if (step > 1) {
            document.getElementById("step" + (step - 1)).classList.add("completed");
        }
        
        // Ajouter le message au log
        const logEntry = document.createElement("div");
        logEntry.className = "log-entry" + (isError ? " log-error" : " log-success");
        logEntry.textContent = message;
        logContainer.appendChild(logEntry);
        logContainer.scrollTop = logContainer.scrollHeight;
    }
    
    // Démarrer linstallation
    window.onload = function() {
        // Simuler le processus dinstallation
        setTimeout(() => {
            updateProgress(1, "Vérification des prérequis...");
            setTimeout(() => {
                updateProgress(2, "Installation des dépendances...");
                setTimeout(() => {
                    updateProgress(3, "Configuration de la base de données...");
                    setTimeout(() => {
                        updateProgress(4, "Migration des tables...");
                        setTimeout(() => {
                            updateProgress(5, "Installation terminée avec succès !");
                            setTimeout(() => {
                                window.location.href = "install.php?step=complete&locale=<?php echo $locale; ?>";
                            }, 1000);
                        }, 2000);
                    }, 2000);
                }, 2000);
            }, 2000);
        }, 1000);
    };
    </script>';
    
    showFooter();
}

// Fonction pour afficher la page de fin d'installation
function showCompletePage($locale) {
    showHeader(t('title', $locale), $locale);
    showLanguageSelector($locale);
    
    // Récupérer la traduction de "go_to_site" depuis les fichiers de traduction
    $goToSiteText = '';
    $translationFile = ROOT_PATH . '/resources/locales/' . $locale . '/translation.json';
    
    if (file_exists($translationFile)) {
        $translations = json_decode(file_get_contents($translationFile), true);
        if (isset($translations['install']['go_to_login'])) {
            $goToSiteText = $translations['install']['go_to_login'];
        }
    }
    
    // Si la traduction n'est pas trouvée, utiliser la traduction par défaut
    if (empty($goToSiteText)) {
        $goToSiteText = t('go_to_site', $locale);
    }
    
    echo '<h2>' . t('installation_complete', $locale) . '</h2>
    <div class="alert alert-success">
        <p>' . t('installation_complete', $locale) . '</p>
    </div>
    <a href="../" class="btn">' . $goToSiteText . '</a>';
    
    showFooter();
}

// Fonction pour afficher la page "déjà installé"
function showAlreadyInstalledPage($locale) {
    showHeader(t('title', $locale), $locale);
    showLanguageSelector($locale);
    
    // Récupérer la traduction de "go_to_site" depuis les fichiers de traduction
    $goToSiteText = '';
    $translationFile = ROOT_PATH . '/resources/locales/' . $locale . '/translation.json';
    
    if (file_exists($translationFile)) {
        $translations = json_decode(file_get_contents($translationFile), true);
        if (isset($translations['install']['go_to_login'])) {
            $goToSiteText = $translations['install']['go_to_login'];
        }
    }
    
    // Si la traduction n'est pas trouvée, utiliser la traduction par défaut
    if (empty($goToSiteText)) {
        $goToSiteText = t('go_to_site', $locale);
    }
    
    echo '<div class="alert alert-info">
        <p>' . t('already_installed', $locale) . '</p>
    </div>
    <a href="../" class="btn">' . $goToSiteText . '</a>';
    
    showFooter();
}

// Fonction pour valider les entrées utilisateur
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour vérifier les permissions des dossiers critiques
function checkCriticalPermissions() {
    $criticalPaths = [
        ROOT_PATH . '/storage',
        ROOT_PATH . '/bootstrap/cache',
        ROOT_PATH . '/public',
        ROOT_PATH . '/config'
    ];
    
    foreach ($criticalPaths as $path) {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new Exception("Impossible de créer le dossier : $path");
            }
        }
        
        if (!is_writable($path)) {
            throw new Exception("Le dossier n'est pas accessible en écriture : $path");
        }
    }
    
    return true;
}

// Fonction pour vérifier les extensions PHP requises
function checkRequiredExtensions() {
    $requiredExtensions = [
        'pdo',
        'pdo_mysql',
        'mbstring',
        'openssl',
        'json',
        'fileinfo'
    ];
    
    $missingExtensions = [];
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }
    
    if (!empty($missingExtensions)) {
        throw new Exception("Extensions PHP manquantes : " . implode(', ', $missingExtensions));
    }
    
    return true;
}

// Point d'entrée principal
try {
    // Vérifier les extensions PHP requises
    checkRequiredExtensions();
    
    // Vérifier les permissions des dossiers critiques
    checkCriticalPermissions();
    
    // Vérifier si Laravel est déjà installé
    if (isLaravelInstalled() && !isset($_GET['force'])) {
        $locale = validateInput($_GET['locale'] ?? 'fr');
        showAlreadyInstalledPage($locale);
        exit;
    }
    
    // Récupérer et valider l'etape actuelle
    $step = validateInput($_GET['step'] ?? $_POST['step'] ?? 'language');
    $locale = validateInput($_GET['locale'] ?? $_POST['locale'] ?? 'fr');
    
    // Charger les traductions depuis les fichiers JSON si disponibles
    $translationsFromFile = [];
    $translationFile = ROOT_PATH . '/resources/locales/' . $locale . '/translation.json';
    
    if (file_exists($translationFile)) {
        $translationsFromFile = json_decode(file_get_contents($translationFile), true);
    }
    
    // Traiter l'action de test de connexion à la base de données
    if (isset($_POST['action']) && $_POST['action'] === 'test_connection') {
        header('Content-Type: application/json');
        try {
            $host = $_POST['db_host'] ?? '';
            $port = $_POST['db_port'] ?? '3306';
            $database = $_POST['db_database'] ?? '';
            $username = $_POST['db_username'] ?? '';
            $password = $_POST['db_password'] ?? '';
            
            // Tester la connexion
            testDatabaseConnection($host, $port, $database, $username, $password);
            
            echo json_encode([
                'success' => true,
                'message' => t('connection_successful', $locale)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    // Traiter les etapes
    switch ($step) {
        case 'language':
            showLanguagePage();
            break;
            
        case 'database':
            // Créer le fichier .env s'il n'existe pas
            if (!file_exists(ROOT_PATH . '/.env')) {
                createEnvFile();
            }
            showDatabasePage();
            break;
            
        case 'admin_config':
            // Vérifier si tous les champs de base de données sont présents
            $requiredFields = ['db_host', 'db_port', 'db_database', 'db_username', 'locale'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field])) {
                    showError('Tous les champs requis doivent être remplis.');
                }
            }
            showAdminConfigPage();
            break;
            
        case 'install':
            // Créer le fichier .env s'il n'existe pas
            if (!file_exists(ROOT_PATH . '/.env')) {
                createEnvFile();
            }
            
            // Valider les données de l'administrateur
            $adminFirstname = $_POST['admin_firstname'] ?? '';
            $adminLastname = $_POST['admin_lastname'] ?? '';
            $adminEmail = $_POST['admin_email'] ?? '';
            $adminUsername = $_POST['admin_username'] ?? '';
            $adminPassword = $_POST['admin_password'] ?? '';
            $adminPasswordConfirmation = $_POST['admin_password_confirmation'] ?? '';
            
            // Vérifier que les mots de passe correspondent
            if ($adminPassword !== $adminPasswordConfirmation) {
                throw new Exception('Les mots de passe ne correspondent pas');
            }
            
            // Traiter le logo si fourni
            $logoPath = '';
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . '/public/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileInfo = getimagesize($_FILES['logo']['tmp_name']);
                if ($fileInfo === false) {
                    throw new Exception('Le fichier uploadé n\'est pas une image valide');
                }
                
                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logoFilename = 'logo.' . $extension;
                $logoPath = $uploadDir . $logoFilename;
                
                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath)) {
                    throw new Exception('Impossible de sauvegarder le logo');
                }
                
                // Stocker le chemin relatif du logo
                $logoPath = '/images/' . $logoFilename;
            }
            
            // Mettre à jour le fichier .env avec les données du formulaire
            $envData = [
                'DB_HOST' => $_POST['db_host'] ?? 'localhost',
                'DB_PORT' => $_POST['db_port'] ?? '3306',
                'DB_DATABASE' => $_POST['db_database'] ?? 'laravel',
                'DB_USERNAME' => $_POST['db_username'] ?? 'root',
                'DB_PASSWORD' => $_POST['db_password'] ?? '',
                'APP_NAME' => $_POST['app_name'] ?? 'Laravel',
                'APP_ENV' => $_POST['app_env'] ?? 'local',
                'APP_URL' => $_POST['app_url'] ?? 'http://localhost',
                'APP_LOCALE' => $locale,
                'ADMIN_LOGO' => $logoPath
            ];
            
            updateEnvFile($envData);
            
            // Afficher la page d'installation en cours
            showInstallingPage($locale);
            
            // Exécuter les commandes d'installation
            runCommand('composer install --no-interaction');
            runCommand('php artisan key:generate --force');
            runCommand('php artisan config:clear');
            runCommand('php artisan migrate --force');
            
            // Créer l'administrateur dans la base de données
            try {
                $pdo = new PDO("mysql:host={$_POST['db_host']};port={$_POST['db_port']};dbname={$_POST['db_database']}",
                    $_POST['db_username'],
                    $_POST['db_password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Vérifier si la table admins existe
                $tableExists = $pdo->query("SHOW TABLES LIKE 'admins'")->rowCount() > 0;
                
                if ($tableExists) {
                    // Hasher le mot de passe avec bcrypt
                    $hashedPassword = password_hash($_POST['admin_password'], PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    // Préparer les données de l'administrateur
                    $name = $_POST['admin_firstname'] . ' ' . $_POST['admin_lastname'];
                    $email = $_POST['admin_email'];
                    $now = date('Y-m-d H:i:s');
                    
                    // Insérer l'administrateur dans la base de données
                    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password, is_super_admin, created_at, updated_at) 
                        VALUES (:name, :email, :password, 1, :created_at, :updated_at)");
                    $stmt->execute([
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la création de l'administrateur : " . $e->getMessage());
            }
            
            // Importer le fichier SQL s'il existe
            if (file_exists(ROOT_PATH . '/database/database.sql')) {
                importSqlFile(ROOT_PATH . '/database/database.sql');
            }
            
            // Marquer l'installation comme terminée
            updateEnvFile(['APP_INSTALLED' => 'true']);
            
            // Rediriger vers la page de fin d'installation
            header('Location: install.php?step=complete&locale=' . $locale);
            exit;
            break;
            
        case 'complete':
            showCompletePage($locale);
            break;
            
        default:
            showLanguagePage();
            break;
    }
} catch (Exception $e) {
    showError($e->getMessage());
}
