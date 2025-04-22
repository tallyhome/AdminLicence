<?php
/**
 * Fonctions essentielles pour l'installateur
 */

/**
 * Crée un fichier de log pour suivre les erreurs
 * 
 * @param string $message Message à logger
 * @param string $type Type de message (INFO, WARNING, ERROR, FATAL)
 * @return void
 */
function writeToLog($message, $type = 'INFO') {
    $logFile = INSTALL_PATH . '/install_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Vérifie si Laravel est déjà installé
 * 
 * @return bool True si Laravel est déjà installé, false sinon
 */
function isLaravelInstalled() {
    // Si le paramètre force est présent, considérer que l'application n'est pas installée
    if (isset($_GET['force'])) {
        return false;
    }
    
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
            $requiredTables = ['migrations', 'admins'];
            $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($requiredTables as $table) {
                if (!in_array($table, $existingTables)) {
                    return false;
                }
            }
        } catch (Exception $e) {
            writeToLog("Erreur de connexion à la base de données: " . $e->getMessage(), 'ERROR');
            return false;
        }

        // Si toutes les vérifications sont passées, l'application est considérée comme installée
        return true;
    } catch (Exception $e) {
        writeToLog("Erreur lors de la vérification de l'installation: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Génère une clé d'application sécurisée
 * 
 * @return string Clé d'application au format base64
 */
function generateAppKey() {
    return 'base64:' . base64_encode(random_bytes(32));
}

/**
 * Vérifie la validité d'une licence
 * 
 * @param string $cleSeriale Clé de licence à vérifier
 * @param string|null $domaine Domaine pour lequel vérifier la licence
 * @param string|null $adresseIP Adresse IP pour laquelle vérifier la licence
 * @return array Résultat de la vérification
 */
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
