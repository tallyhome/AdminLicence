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
        // Récupérer la taille des logs
        $logsSize = $this->getDirectorySize(public_path('install/logs'));
        
        // Récupérer la taille des images
        $imagesSize = $this->getDirectorySize(public_path('images'));
        
        // Récupérer la liste des assets CSS/JS
        $cssFiles = $this->getAssetsList('css');
        $jsFiles = $this->getAssetsList('js');
        
        return view('admin.settings.optimization', compact(
            'logsSize',
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
            
            // Exécuter le script de nettoyage des logs
            $process = new Process(['php', public_path('install/clean-logs.php')]);
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            $output = $process->getOutput();
            
            // Journaliser le résultat
            Log::info('Nettoyage des logs effectué avec succès', [
                'output' => $output
            ]);
            
            return redirect()->route('admin.settings.optimization')
                ->with('success', 'Les fichiers de logs ont été nettoyés avec succès.')
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
        if (!File::isDirectory($directory)) {
            return '0 B';
        }
        
        $size = 0;
        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }
        
        return $this->formatBytes($size);
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
}
