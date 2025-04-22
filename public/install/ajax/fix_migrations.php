<?php
/**
 * Script pour corriger les problèmes de migrations
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Augmenter la limite de temps d'exécution
set_time_limit(300);

// Créer le répertoire de logs s'il n'existe pas
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Fonction pour journaliser les informations
function logInfo($message) {
    global $logDir;
    $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
    file_put_contents($logDir . '/fix_migrations.log', $logMessage, FILE_APPEND);
    echo $logMessage;
}

// Fonction pour trouver les fichiers de migration
function findMigrationFiles($directory, $pattern) {
    $files = [];
    $dir = new DirectoryIterator($directory);
    
    foreach ($dir as $fileInfo) {
        if (!$fileInfo->isDot() && !$fileInfo->isDir()) {
            $filename = $fileInfo->getFilename();
            if (preg_match($pattern, $filename)) {
                $files[] = $fileInfo->getPathname();
            }
        }
    }
    
    return $files;
}

// Fonction pour exécuter une commande et capturer la sortie
function executeCommand($command, $cwd = null) {
    $output = [];
    $returnVar = 0;
    
    $descriptorspec = [
        0 => ["pipe", "r"],  // stdin
        1 => ["pipe", "w"],  // stdout
        2 => ["pipe", "w"]   // stderr
    ];
    
    $process = proc_open($command, $descriptorspec, $pipes, $cwd);
    
    if (is_resource($process)) {
        // Fermer stdin
        fclose($pipes[0]);
        
        // Lire stdout
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        
        // Lire stderr
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        // Fermer le processus et récupérer le code de retour
        $returnVar = proc_close($process);
        
        $output = [
            'stdout' => $stdout,
            'stderr' => $stderr,
            'return_var' => $returnVar
        ];
    }
    
    return $output;
}

// Chemin racine du projet
$projectRoot = realpath(__DIR__ . '/../../../');
$databasePath = $projectRoot . '/database';
$migrationsPath = $databasePath . '/migrations';

logInfo('Début de la correction des migrations');

// 1. Trouver les fichiers de migration problématiques
$cacheTableMigrationPattern = '/.*create_cache_table\.php$/';
$cacheTableMigrationFiles = findMigrationFiles($migrationsPath, $cacheTableMigrationPattern);

logInfo('Fichiers de migration pour la table cache trouvés : ' . json_encode($cacheTableMigrationFiles));

// 2. Modifier les fichiers de migration problématiques
foreach ($cacheTableMigrationFiles as $file) {
    $content = file_get_contents($file);
    
    // Modifier la méthode up() pour vérifier si la table existe déjà
    $newContent = preg_replace(
        '/public function up\(\)\s*\{(.*?)Schema::create\(\'cache\',/s',
        'public function up()
    {
        // Skip creation if table already exists
        if (Schema::hasTable(\'cache\')) {
            return;
        }
        
        Schema::create(\'cache\',',
        $content
    );
    
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        logInfo('Migration modifiée : ' . basename($file));
    } else {
        logInfo('Impossible de modifier la migration : ' . basename($file));
    }
}

// 3. Rechercher et modifier d'autres migrations problématiques
$problematicTables = [
    'cache_locks',
    'jobs',
    'failed_jobs',
    'password_reset_tokens',
    'personal_access_tokens',
    'sessions'
];

foreach ($problematicTables as $table) {
    $pattern = '/.*create_' . $table . '_table\.php$/';
    $files = findMigrationFiles($migrationsPath, $pattern);
    
    logInfo('Fichiers de migration pour la table ' . $table . ' trouvés : ' . json_encode($files));
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        // Modifier la méthode up() pour vérifier si la table existe déjà
        $newContent = preg_replace(
            '/public function up\(\)\s*\{(.*?)Schema::create\(\'' . $table . '\',/s',
            'public function up()
    {
        // Skip creation if table already exists
        if (Schema::hasTable(\'' . $table . '\')) {
            return;
        }
        
        Schema::create(\'' . $table . '\',',
            $content
        );
        
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            logInfo('Migration modifiée : ' . basename($file));
        } else {
            logInfo('Impossible de modifier la migration : ' . basename($file));
        }
    }
}

// 4. Exécuter les migrations
logInfo('Exécution des migrations...');
$migrateOutput = executeCommand('php artisan migrate --force', $projectRoot);
logInfo('Résultat des migrations : ' . $migrateOutput['stdout'] . $migrateOutput['stderr']);

// 5. Exécuter les seeders
logInfo('Exécution des seeders...');
$seedOutput = executeCommand('php artisan db:seed --force', $projectRoot);
logInfo('Résultat des seeders : ' . $seedOutput['stdout'] . $seedOutput['stderr']);

// 6. Nettoyer le cache
logInfo('Nettoyage du cache...');
$optimizeOutput = executeCommand('php artisan optimize:clear', $projectRoot);
logInfo('Résultat du nettoyage : ' . $optimizeOutput['stdout'] . $optimizeOutput['stderr']);

logInfo('Correction des migrations terminée');

echo "Correction des migrations terminée. Consultez le fichier de log pour plus de détails.";
