<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OptimizationController extends Controller
{
    /**
     * Affiche la page des outils d'optimisation
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupérer la taille des logs d'installation
        $installLogsSize = $this->getDirectorySize(public_path('install/logs'));
        
        // Récupérer la taille des logs Laravel
        $laravelLogsSize = $this->getDirectorySize(storage_path('logs'));
        
        // Taille totale des logs
        $logsSize = $this->formatBytes($this->getDirectorySizeInBytes(public_path('install/logs')) + 
                                        $this->getDirectorySizeInBytes(storage_path('logs')));
        
        // Récupérer la liste des fichiers de logs
        $installLogFiles = $this->getLogFiles('install');
        $laravelLogFiles = $this->getLogFiles('laravel');
        
        // Récupérer la taille des images
        $imagesSize = $this->getDirectorySize(public_path('images'));
        
        // Récupérer la liste des assets CSS/JS
        $cssFiles = $this->getAssetsList('css');
        $jsFiles = $this->getAssetsList('js');
        
        return view('admin.settings.optimization', compact(
            'logsSize',
            'installLogsSize',
            'laravelLogsSize',
            'installLogFiles',
            'laravelLogFiles',
            'imagesSize',
            'cssFiles',
            'jsFiles'
        ));
    }
    
    /**
     * Nettoie les fichiers de logs
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cleanLogs(Request $request)
    {
        try {
            $output = [];
            $returnCode = 0;
            
            // Vérifier si on doit tout supprimer
            $deleteAll = $request->has('delete_all');
            
            // Vérifier quel type de logs nettoyer
            $logType = $request->input('log_type', 'all');
            
            // Exécuter le script de nettoyage des logs avec les paramètres appropriés
            $command = ['php', public_path('install/clean-logs.php')];
            
            if ($deleteAll) {
                $command[] = '--delete-all';
            }
            
            if ($logType !== 'all') {
                $command[] = '--type=' . $logType;
            }
            
            $process = new Process($command);
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            $output = $process->getOutput();
            
            // Journaliser le résultat
            Log::info('Nettoyage des logs effectué avec succès', [
                'output' => $output,
                'delete_all' => $deleteAll,
                'log_type' => $logType
            ]);
            
            $typeLabel = $logType === 'install' ? 'd\'installation' : 
                        ($logType === 'laravel' ? 'Laravel' : '');
            
            $message = $deleteAll ? 
                'Tous les fichiers de logs' . ($typeLabel ? ' ' . $typeLabel : '') . ' ont été supprimés avec succès.' : 
                'Les fichiers de logs' . ($typeLabel ? ' ' . $typeLabel : '') . ' ont été nettoyés avec succès.';
            
            return redirect()->route('admin.settings.optimization')
                ->with('success', $message)
                ->with('output', $output);
                
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage des logs', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.settings.optimization')
                ->with('error', 'Erreur lors du nettoyage des logs: ' . $e->getMessage());
        }
    }
    
    /**
     * Optimise les images
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function optimizeImages(Request $request)
    {
        try {
            // Exécuter la commande d'optimisation des images
            Artisan::call('images:optimize', [
                '--force' => $request->has('force'),
                '--quality' => $request->input('quality', 80)
            ]);
            
            $output = Artisan::output();
            
            // Journaliser le résultat
            Log::info('Optimisation des images effectuée avec succès', [
                'output' => $output
            ]);
            
            return redirect()->route('admin.settings.optimization')
                ->with('success', 'Les images ont été optimisées avec succès.')
                ->with('output', $output);
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'optimisation des images', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.settings.optimization')
                ->with('error', 'Erreur lors de l\'optimisation des images: ' . $e->getMessage());
        }
    }
    
    /**
     * Génère un exemple d'utilisation des assets versionnés
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateAssetExample(Request $request)
    {
        $assetPath = $request->input('asset_path');
        
        if (empty($assetPath)) {
            return redirect()->route('admin.settings.optimization')
                ->with('error', 'Veuillez spécifier un chemin d\'asset.');
        }
        
        $extension = pathinfo($assetPath, PATHINFO_EXTENSION);
        
        if ($extension === 'css') {
            $example = "@versionedCss('{$assetPath}')";
        } elseif ($extension === 'js') {
            $example = "@versionedJs('{$assetPath}')";
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
            $example = "@versionedImage('{$assetPath}')";
        } else {
            $example = "@versionedAsset('{$assetPath}')";
        }
        
        return redirect()->route('admin.settings.optimization')
            ->with('success', 'Exemple généré avec succès.')
            ->with('example', $example);
    }
    
    /**
     * Calcule la taille d'un répertoire
     *
     * @param string $directory Chemin du répertoire
     * @return string Taille formatée
     */
    protected function getDirectorySize($directory)
    {
        return $this->formatBytes($this->getDirectorySizeInBytes($directory));
    }
    
    /**
     * Calcule la taille d'un répertoire en octets
     *
     * @param string $directory Chemin du répertoire
     * @return int Taille en octets
     */
    protected function getDirectorySizeInBytes($directory)
    {
        if (!File::isDirectory($directory)) {
            return 0;
        }
        
        $size = 0;
        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    /**
     * Récupère la liste des assets
     *
     * @param string $type Type d'assets (css, js)
     * @return array Liste des assets
     */
    protected function getAssetsList($type)
    {
        $assets = [];
        $directory = public_path($type);
        
        if (File::isDirectory($directory)) {
            foreach (File::allFiles($directory) as $file) {
                if ($file->getExtension() === $type) {
                    $assets[] = $type . '/' . $file->getFilename();
                }
            }
        }
        
        return $assets;
    }
    
    /**
     * Formate une taille en octets en une chaîne lisible
     *
     * @param int $bytes Taille en octets
     * @param int $precision Précision décimale
     * @return string Taille formatée
     */
    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    /**
     * Récupère la liste des fichiers de logs avec leurs informations
     *
     * @param string $type Type de logs (install ou laravel)
     * @return array Liste des fichiers de logs
     */
    protected function getLogFiles($type = 'install')
    {
        $logsDir = $type === 'install' ? public_path('install/logs') : storage_path('logs');
        $basePath = $type === 'install' ? 'install/logs' : 'storage/logs';
        $logFiles = [];
        
        if (!File::isDirectory($logsDir)) {
            return $logFiles;
        }
        
        foreach (File::files($logsDir) as $file) {
            if ($file->getExtension() === 'log') {
                $logFiles[] = [
                    'name' => $file->getFilename(),
                    'path' => $basePath . '/' . $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'date' => date('Y-m-d H:i:s', $file->getMTime()),
                    'age' => floor((time() - $file->getMTime()) / (60 * 60 * 24)), // âge en jours
                    'type' => $type
                ];
            }
        }
        
        // Trier par date de modification (plus récent en premier)
        usort($logFiles, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $logFiles;
    }
    
    /**
     * Affiche le contenu d'un fichier de log
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function viewLog(Request $request)
    {
        $logPath = $request->input('path');
        
        // Déterminer si c'est un log Laravel ou d'installation
        if (str_starts_with($logPath, 'storage/logs/')) {
            $fullPath = base_path($logPath);
            $validPath = str_starts_with($logPath, 'storage/logs/') && str_ends_with($logPath, '.log');
        } else {
            $fullPath = public_path($logPath);
            $validPath = str_starts_with($logPath, 'install/logs/') && str_ends_with($logPath, '.log');
        }
        
        // Vérifier que le chemin est valide et dans le dossier logs
        if (!File::exists($fullPath) || !$validPath) {
            abort(404, 'Fichier de log non trouvé');
        }
        
        $content = File::get($fullPath);
        
        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
