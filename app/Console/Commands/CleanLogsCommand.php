<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean {--days=30 : Nombre de jours de logs à conserver} {--size=100 : Taille maximale des fichiers de logs en MB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nettoie les fichiers de logs anciens et volumineux';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début du nettoyage des logs...');
        
        $daysToKeep = $this->option('days');
        $maxSizeMB = $this->option('size');
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;
        
        $logsPath = storage_path('logs');
        $logFiles = File::files($logsPath);
        $now = Carbon::now();
        $totalCleaned = 0;
        $totalSize = 0;
        
        foreach ($logFiles as $file) {
            $filePath = $file->getPathname();
            $fileSize = $file->getSize();
            $totalSize += $fileSize;
            
            // Vérifier si le fichier est trop ancien
            $lastModified = Carbon::createFromTimestamp($file->getMTime());
            $daysDiff = $now->diffInDays($lastModified);
            
            // Vérifier si le fichier est trop volumineux
            $isTooLarge = $fileSize > $maxSizeBytes;
            
            if ($daysDiff > $daysToKeep) {
                // Supprimer les fichiers trop anciens
                File::delete($filePath);
                $this->info("Fichier supprimé (trop ancien) : {$filePath} ({$daysDiff} jours)");
                $totalCleaned++;
            } elseif ($isTooLarge) {
                // Pour les fichiers trop volumineux mais récents, on les tronque
                // On garde les dernières lignes (plus récentes)
                $this->truncateLogFile($filePath, $maxSizeBytes / 2);
                $this->info("Fichier tronqué (trop volumineux) : {$filePath} (" . round($fileSize / 1024 / 1024, 2) . " MB)");
                $totalCleaned++;
            }
        }
        
        $this->info("Nettoyage terminé. {$totalCleaned} fichiers traités.");
        $this->info("Taille totale des logs : " . round($totalSize / 1024 / 1024, 2) . " MB");
        
        // Journaliser le résultat du nettoyage
        Log::info("Nettoyage des logs terminé", [
            'files_cleaned' => $totalCleaned,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ]);
    }
    
    /**
     * Tronque un fichier de log en conservant les dernières lignes
     * 
     * @param string $filePath
     * @param int $targetSize
     * @return void
     */
    protected function truncateLogFile($filePath, $targetSize)
    {
        $content = file_get_contents($filePath);
        
        if (strlen($content) <= $targetSize) {
            return;
        }
        
        // Trouver un point de coupure à une nouvelle ligne
        $cutPoint = strlen($content) - $targetSize;
        $cutPoint = strpos($content, "\n", $cutPoint);
        
        if ($cutPoint === false) {
            $cutPoint = $targetSize;
        }
        
        // Conserver uniquement la partie la plus récente
        $newContent = "--- FICHIER LOG TRONQUÉ LE " . date('Y-m-d H:i:s') . " ---\n\n";
        $newContent .= substr($content, $cutPoint);
        
        file_put_contents($filePath, $newContent);
    }
}
