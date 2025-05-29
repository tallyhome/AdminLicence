<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class OptimizeImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize {--force : Force optimization even if already optimized} {--quality=80 : JPEG/PNG quality (0-100)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimise les images dans le dossier public/images';

    /**
     * Extensions d'images supportées
     *
     * @var array
     */
    protected $supportedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de l\'optimisation des images...');
        
        $imagesPath = public_path('images');
        $quality = $this->option('quality');
        $force = $this->option('force');
        
        if (!is_dir($imagesPath)) {
            $this->error('Le dossier images n\'existe pas!');
            return 1;
        }
        
        $this->info('Analyse du dossier: ' . $imagesPath);
        
        // Vérifier si GD est installé
        if (!extension_loaded('gd')) {
            $this->error('L\'extension GD n\'est pas installée. Impossible d\'optimiser les images.');
            return 1;
        }
        
        // Récupérer toutes les images
        $images = $this->getAllImages($imagesPath);
        $this->info('Nombre d\'images trouvées: ' . count($images));
        
        $optimizedCount = 0;
        $totalSaved = 0;
        
        $bar = $this->output->createProgressBar(count($images));
        $bar->start();
        
        foreach ($images as $image) {
            $result = $this->optimizeImage($image, $quality, $force);
            if ($result['optimized']) {
                $optimizedCount++;
                $totalSaved += $result['saved'];
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info('Optimisation terminée!');
        $this->info('Images optimisées: ' . $optimizedCount . '/' . count($images));
        $this->info('Espace disque économisé: ' . $this->formatBytes($totalSaved));
        
        // Journaliser le résultat
        Log::info('Optimisation des images terminée', [
            'total_images' => count($images),
            'optimized_images' => $optimizedCount,
            'bytes_saved' => $totalSaved
        ]);
        
        return 0;
    }
    
    /**
     * Récupère toutes les images dans un dossier et ses sous-dossiers
     *
     * @param string $directory Chemin du dossier à analyser
     * @return array Liste des chemins des images
     */
    protected function getAllImages($directory)
    {
        $images = [];
        $files = File::allFiles($directory);
        
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, $this->supportedExtensions)) {
                $images[] = $file->getPathname();
            }
        }
        
        return $images;
    }
    
    /**
     * Optimise une image
     *
     * @param string $imagePath Chemin de l'image à optimiser
     * @param int $quality Qualité de l'image (0-100)
     * @param bool $force Forcer l'optimisation même si déjà optimisée
     * @return array Résultat de l'optimisation
     */
    protected function optimizeImage($imagePath, $quality, $force)
    {
        $result = [
            'optimized' => false,
            'saved' => 0
        ];
        
        // Vérifier si l'image est déjà optimisée
        $optimizedFlag = $imagePath . '.optimized';
        if (file_exists($optimizedFlag) && !$force) {
            return $result;
        }
        
        $originalSize = filesize($imagePath);
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        // Créer une image à partir du fichier
        $image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($imagePath);
                break;
            case 'png':
                $image = @imagecreatefrompng($imagePath);
                break;
            case 'gif':
                $image = @imagecreatefromgif($imagePath);
                break;
        }
        
        if (!$image) {
            return $result;
        }
        
        // Créer un fichier temporaire
        $tempFile = $imagePath . '.temp';
        
        // Sauvegarder l'image optimisée
        $success = false;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $success = imagejpeg($image, $tempFile, $quality);
                break;
            case 'png':
                // Pour PNG, la qualité est de 0 (pas de compression) à 9 (compression maximale)
                $pngQuality = round((100 - $quality) / 11.1);
                $success = imagepng($image, $tempFile, $pngQuality);
                break;
            case 'gif':
                $success = imagegif($image, $tempFile);
                break;
        }
        
        // Libérer la mémoire
        imagedestroy($image);
        
        if ($success) {
            $newSize = filesize($tempFile);
            
            // Ne remplacer que si la nouvelle image est plus petite
            if ($newSize < $originalSize) {
                File::move($tempFile, $imagePath);
                touch($optimizedFlag);
                
                $result['optimized'] = true;
                $result['saved'] = $originalSize - $newSize;
            } else {
                // Supprimer le fichier temporaire
                File::delete($tempFile);
            }
        } else {
            // Supprimer le fichier temporaire s'il existe
            if (file_exists($tempFile)) {
                File::delete($tempFile);
            }
        }
        
        return $result;
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
