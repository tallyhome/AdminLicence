<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SerialKey;
use App\Services\LicenceHistoryService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckExpiredLicencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licence:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie et marque les licences expirées';

    /**
     * @var LicenceHistoryService
     */
    protected $historyService;

    /**
     * Create a new command instance.
     */
    public function __construct(LicenceHistoryService $historyService)
    {
        parent::__construct();
        $this->historyService = $historyService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Vérification des licences expirées...');
        
        $now = Carbon::now();
        $expiredCount = 0;
        
        // Récupérer toutes les clés actives avec une date d'expiration
        $expiredKeys = SerialKey::where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->get();
            
        foreach ($expiredKeys as $key) {
            // Marquer la clé comme expirée
            $key->status = 'expired';
            $key->save();
            
            // Enregistrer l'action dans l'historique
            $this->historyService->logAction($key, 'expired', [
                'expired_at' => $now->toDateTimeString(),
                'previous_expiry' => $key->expires_at,
                'automatic' => true
            ]);
            
            $projectName = 'N/A';
            if (isset($key->project) && isset($key->project->name)) {
                $projectName = $key->project->name;
            }
            $this->info("Clé expirée marquée : {$key->serial_key} (Projet: {$projectName})");
            $expiredCount++;
        }
        
        $this->info("Vérification terminée. {$expiredCount} licences expirées trouvées et marquées.");
        
        // Journaliser le résultat minimal
        if ($expiredCount > 0) {
            Log::info("Licences expirées marquées", [
                'count' => $expiredCount
            ]);
        } else {
            // En mode debug uniquement pour les résultats sans action
            if (env('APP_ENV') === 'local' || env('APP_DEBUG') === true) {
                Log::debug("Aucune licence expirée trouvée");
            }
        }
        
        return 0;
    }
}
