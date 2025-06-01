<?php

namespace App\Console\Commands;

use App\Services\EncryptionService;
use Illuminate\Console\Command;

class ReencryptLicenceKeys extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * @var string
     */
    protected $signature = 'licence:reencrypt-keys {--force : Forcer le rechiffrement sans confirmation}';

    /**
     * La description de la commande console.
     *
     * @var string
     */
    protected $description = 'Rechiffre toutes les clés de licence dans la base de données pour améliorer la sécurité';

    /**
     * @var EncryptionService
     */
    protected $encryptionService;

    /**
     * Créer une nouvelle instance de commande.
     *
     * @param EncryptionService $encryptionService
     * @return void
     */
    public function __construct(EncryptionService $encryptionService)
    {
        parent::__construct();
        $this->encryptionService = $encryptionService;
    }

    /**
     * Exécuter la commande console.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Vérification des clés de licence...');
        
        // Vérifier si le chiffrement est activé
        if (!env('SECURITY_ENCRYPT_LICENCE_KEYS', true)) {
            $this->warn('Le chiffrement des clés de licence est désactivé dans la configuration.');
            
            if (!$this->option('force') && !$this->confirm('Voulez-vous activer le chiffrement et continuer?')) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
            
            $this->info('Activation du chiffrement des clés de licence...');
        }
        
        // Demander confirmation sauf si l'option --force est utilisée
        if (!$this->option('force') && !$this->confirm('Cette opération va rechiffrer toutes les clés de licence. Voulez-vous continuer?')) {
            $this->info('Opération annulée.');
            return Command::SUCCESS;
        }
        
        $this->info('Rechiffrement des clés de licence en cours...');
        
        // Créer une sauvegarde de la base de données avant de procéder
        $this->call('db:backup', ['--database' => env('DB_CONNECTION', 'sqlite')]);
        
        // Rechiffrer toutes les clés
        $result = $this->encryptionService->reencryptAllLicenceKeys();
        
        if ($result['success']) {
            $this->info($result['message']);
            $this->info('Opération terminée avec succès.');
            return Command::SUCCESS;
        } else {
            $this->error($result['message']);
            $this->error('Opération échouée.');
            return Command::FAILURE;
        }
    }
}
