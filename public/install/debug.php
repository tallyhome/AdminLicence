<?php
/**
 * Script de débogage pour l'installation
 */

// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir le type de contenu comme HTML
header('Content-Type: text/html; charset=utf-8');

// Créer le répertoire de logs s'il n'existe pas
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Fonction pour vérifier l'existence d'un fichier ou répertoire
function checkPath($path, $type = 'file') {
    $exists = $type === 'file' ? file_exists($path) : is_dir($path);
    return [
        'path' => $path,
        'exists' => $exists,
        'type' => $type,
        'readable' => is_readable($path),
        'writable' => is_writable($path),
    ];
}

// Chemins à vérifier
$projectRoot = realpath(__DIR__ . '/../..');
$parentRoot = realpath($projectRoot . '/..');

$paths = [
    // Chemin actuel
    'current_dir' => __DIR__,
    
    // Remonter d'un niveau (public)
    'public_dir' => realpath(__DIR__ . '/..'),
    
    // Remonter de deux niveaux (racine du projet)
    'project_root' => $projectRoot,
    
    // Remonter de trois niveaux (parent du projet)
    'parent_root' => $parentRoot,
    
    // Vérifier les fichiers/répertoires importants
    'env_file_project' => checkPath($projectRoot . '/.env'),
    'env_file_parent' => checkPath($parentRoot . '/.env'),
    'vendor_dir_project' => checkPath($projectRoot . '/vendor', 'directory'),
    'vendor_dir_parent' => checkPath($parentRoot . '/vendor', 'directory'),
    'artisan_project' => checkPath($projectRoot . '/artisan'),
    'artisan_parent' => checkPath($parentRoot . '/artisan'),
    'routes_dir_project' => checkPath($projectRoot . '/routes', 'directory'),
    'routes_dir_parent' => checkPath($parentRoot . '/routes', 'directory'),
    'web_php_project' => checkPath($projectRoot . '/routes/web.php'),
    'web_php_parent' => checkPath($parentRoot . '/routes/web.php'),
];

// Journaliser les résultats
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'function' => 'debug_paths',
    'paths' => $paths
];
file_put_contents($logDir . '/debug_paths.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Débogage de l'installation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        .section { margin-bottom: 20px; }
        .path-info { margin-bottom: 10px; padding: 10px; border-radius: 5px; }
        .exists { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .not-exists { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .true { color: green; }
        .false { color: red; }
    </style>
</head>
<body>
    <h1>Débogage de l'installation</h1>
    
    <div class="section">
        <h2>Chemins vérifiés</h2>
        <p>Répertoire actuel: <strong><?php echo __DIR__; ?></strong></p>
        <p>Répertoire public: <strong><?php echo realpath(__DIR__ . '/..'); ?></strong></p>
        <p>Racine du projet: <strong><?php echo $projectRoot; ?></strong></p>
        <p>Parent du projet: <strong><?php echo $parentRoot; ?></strong></p>
    </div>
    
    <div class="section">
        <h2>Vérification des fichiers et répertoires</h2>
        <table>
            <tr>
                <th>Élément</th>
                <th>Chemin</th>
                <th>Existe</th>
                <th>Lisible</th>
                <th>Modifiable</th>
            </tr>
            <?php foreach ($paths as $key => $value): ?>
                <?php if (is_array($value)): ?>
                    <tr>
                        <td><?php echo $key; ?></td>
                        <td><?php echo $value['path']; ?></td>
                        <td class="<?php echo $value['exists'] ? 'true' : 'false'; ?>">
                            <?php echo $value['exists'] ? 'Oui' : 'Non'; ?>
                        </td>
                        <td class="<?php echo $value['readable'] ? 'true' : 'false'; ?>">
                            <?php echo $value['readable'] ? 'Oui' : 'Non'; ?>
                        </td>
                        <td class="<?php echo $value['writable'] ? 'true' : 'false'; ?>">
                            <?php echo $value['writable'] ? 'Oui' : 'Non'; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Détection du projet Laravel</h2>
        <p>
            Pour qu'un projet soit détecté comme Laravel, il doit avoir :
            <ul>
                <li>Un fichier artisan à la racine</li>
                <li>Un répertoire routes</li>
                <li>Un fichier routes/web.php</li>
            </ul>
        </p>
        
        <h3>Vérification au niveau du projet (<?php echo $projectRoot; ?>)</h3>
        <?php 
        $isLaravelProject = $paths['artisan_project']['exists'] && 
                            $paths['routes_dir_project']['exists'] && 
                            $paths['web_php_project']['exists'];
        ?>
        <div class="path-info <?php echo $isLaravelProject ? 'exists' : 'not-exists'; ?>">
            <p><strong>Résultat :</strong> <?php echo $isLaravelProject ? 'C\'est un projet Laravel' : 'Ce n\'est PAS un projet Laravel'; ?></p>
        </div>
        
        <h3>Vérification au niveau parent (<?php echo $parentRoot; ?>)</h3>
        <?php 
        $isLaravelParent = $paths['artisan_parent']['exists'] && 
                           $paths['routes_dir_parent']['exists'] && 
                           $paths['web_php_parent']['exists'];
        ?>
        <div class="path-info <?php echo $isLaravelParent ? 'exists' : 'not-exists'; ?>">
            <p><strong>Résultat :</strong> <?php echo $isLaravelParent ? 'C\'est un projet Laravel' : 'Ce n\'est PAS un projet Laravel'; ?></p>
        </div>
    </div>
    
    <div class="section">
        <h2>Vérification du fichier .env</h2>
        <h3>Au niveau du projet (<?php echo $projectRoot; ?>)</h3>
        <div class="path-info <?php echo $paths['env_file_project']['exists'] ? 'exists' : 'not-exists'; ?>">
            <p><strong>Résultat :</strong> <?php echo $paths['env_file_project']['exists'] ? 'Le fichier .env existe' : 'Le fichier .env N\'EXISTE PAS'; ?></p>
            <p><strong>Chemin :</strong> <?php echo $paths['env_file_project']['path']; ?></p>
        </div>
        
        <h3>Au niveau parent (<?php echo $parentRoot; ?>)</h3>
        <div class="path-info <?php echo $paths['env_file_parent']['exists'] ? 'exists' : 'not-exists'; ?>">
            <p><strong>Résultat :</strong> <?php echo $paths['env_file_parent']['exists'] ? 'Le fichier .env existe' : 'Le fichier .env N\'EXISTE PAS'; ?></p>
            <p><strong>Chemin :</strong> <?php echo $paths['env_file_parent']['path']; ?></p>
        </div>
    </div>
    
    <div class="section">
        <h2>Vérification du répertoire vendor</h2>
        <h3>Au niveau du projet (<?php echo $projectRoot; ?>)</h3>
        <div class="path-info <?php echo $paths['vendor_dir_project']['exists'] ? 'exists' : 'not-exists'; ?>">
            <p><strong>Résultat :</strong> <?php echo $paths['vendor_dir_project']['exists'] ? 'Le répertoire vendor existe' : 'Le répertoire vendor N\'EXISTE PAS'; ?></p>
            <p><strong>Chemin :</strong> <?php echo $paths['vendor_dir_project']['path']; ?></p>
        </div>
        
        <h3>Au niveau parent (<?php echo $parentRoot; ?>)</h3>
        <div class="path-info <?php echo $paths['vendor_dir_parent']['exists'] ? 'exists' : 'not-exists'; ?>">
            <p><strong>Résultat :</strong> <?php echo $paths['vendor_dir_parent']['exists'] ? 'Le répertoire vendor existe' : 'Le répertoire vendor N\'EXISTE PAS'; ?></p>
            <p><strong>Chemin :</strong> <?php echo $paths['vendor_dir_parent']['path']; ?></p>
        </div>
    </div>
</body>
</html>
