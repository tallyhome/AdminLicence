<?php
/**
 * Fonctions d'installation pour l'installateur
 */

/**
 * Crée le fichier .env à partir du modèle
 * 
 * @return bool True si le fichier a été créé avec succès, false sinon
 */
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
        writeToLog("Erreur lors de la création du fichier .env: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Met à jour le fichier .env avec les nouvelles valeurs
 * 
 * @param array $data Tableau des valeurs à mettre à jour
 * @return bool True si le fichier a été mis à jour avec succès, false sinon
 */
function updateEnvFile($data) {
    if (!file_exists(ROOT_PATH . '/.env')) {
        writeToLog("Le fichier .env n'existe pas", 'ERROR');
        return false;
    }
    
    try {
        $envContent = file_get_contents(ROOT_PATH . '/.env');
        if ($envContent === false) {
            throw new Exception('Impossible de lire le fichier .env');
        }
        
        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            
            // Échapper les caractères spéciaux dans la valeur
            $escapedValue = addcslashes($value, '$/\\');
            
            if (preg_match($pattern, $envContent)) {
                // Mettre à jour la valeur existante
                $envContent = preg_replace($pattern, "{$key}={$escapedValue}", $envContent);
            } else {
                // Ajouter la nouvelle configuration
                $envContent .= "\n{$key}={$escapedValue}";
            }
        }
        
        if (file_put_contents(ROOT_PATH . '/.env', $envContent) === false) {
            throw new Exception('Impossible d\'écrire dans le fichier .env');
        }
        
        return true;
    } catch (Exception $e) {
        writeToLog("Erreur lors de la mise à jour du fichier .env: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Finalise l'installation
 * 
 * @return bool True si l'installation a été finalisée avec succès, false sinon
 */
function finalizeInstallation() {
    try {
        writeToLog("Finalisation de l'installation");
        
        // Marquer l'application comme installée dans le fichier .env
        if (!updateEnvFile(['APP_INSTALLED' => 'true'])) {
            writeToLog("Impossible de marquer l'application comme installée", 'ERROR');
            return false;
        }
        
        // Créer un fichier pour indiquer que l'installation est terminée
        $installLockPath = ROOT_PATH . '/storage/installed';
        if (!is_dir(dirname($installLockPath))) {
            if (!mkdir(dirname($installLockPath), 0755, true)) {
                writeToLog("Impossible de créer le dossier storage", 'ERROR');
                return false;
            }
        }
        
        if (file_put_contents($installLockPath, date('Y-m-d H:i:s')) === false) {
            writeToLog("Impossible de créer le fichier installed", 'ERROR');
            return false;
        }
        
        writeToLog("Installation finalisée avec succès");
        return true;
    } catch (Exception $e) {
        writeToLog("Erreur lors de la finalisation de l'installation: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * S'assure que le dossier de logs existe
 * 
 * @return bool True si le dossier existe ou a été créé avec succès, false sinon
 */
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
