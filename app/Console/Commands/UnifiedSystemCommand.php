<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenceModeService;
use App\Models\SerialKey;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnifiedSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unified-system:manage 
                            {action : Action à effectuer (init, status, refresh, migrate-data, create-licence)}
                            {--type= : Type de licence pour create-licence (single|saas)}
                            {--project= : ID du projet pour create-licence}
                            {--features= : Fonctionnalités JSON pour create-licence}
                            {--limits= : Limites JSON pour create-licence}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gère le système unifié mono-compte/SaaS';

    protected LicenceModeService $licenceModeService;

    public function __construct(LicenceModeService $licenceModeService)
    {
        parent::__construct();
        $this->licenceModeService = $licenceModeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'init':
                return $this->initializeSystem();
            case 'status':
                return $this->showStatus();
            case 'refresh':
                return $this->refreshSystem();
            case 'migrate-data':
                return $this->migrateExistingData();
            case 'create-licence':
                return $this->createLicence();
            default:
                $this->error("Action inconnue: {$action}");
                $this->info('Actions disponibles: init, status, refresh, migrate-data, create-licence');
                return 1;
        }
    }

    /**
     * Initialise le système unifié
     */
    protected function initializeSystem(): int
    {
        $this->info('🚀 Initialisation du système unifié...');

        try {
            // Exécuter les migrations
            $this->call('migrate');

            // Exécuter le seeder
            $this->call('db:seed', ['--class' => 'UnifiedSystemSeeder']);

            // Rafraîchir le cache
            $this->licenceModeService->refreshMode();

            $this->info('✅ Système unifié initialisé avec succès!');
            $this->showStatus();

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de l\'initialisation: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Affiche le statut du système
     */
    protected function showStatus(): int
    {
        $this->info('📊 Statut du système unifié');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Mode actuel
        $currentMode = $this->licenceModeService->getCurrentMode();
        $this->info("Mode actuel: {$currentMode}");

        // Statistiques des licences
        $totalLicences = SerialKey::count();
        $activeLicences = SerialKey::where('status', 'active')->count();
        $singleLicences = SerialKey::where('licence_type', 'single')->count();
        $multiLicences = SerialKey::where('licence_type', 'multi')->count();
        $saasEnabledLicences = SerialKey::where('is_saas_enabled', true)->count();

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Total licences', $totalLicences],
                ['Licences actives', $activeLicences],
                ['Licences mono-compte', $singleLicences],
                ['Licences multi-comptes', $multiLicences],
                ['Licences SaaS activées', $saasEnabledLicences],
            ]
        );

        // Statistiques des tenants
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $primaryTenants = Tenant::where('is_primary', true)->count();
        $saasTenants = Tenant::where('licence_mode', 'saas')->count();
        $singleTenants = Tenant::where('licence_mode', 'single')->count();

        $this->table(
            ['Métrique Tenants', 'Valeur'],
            [
                ['Total tenants', $totalTenants],
                ['Tenants actifs', $activeTenants],
                ['Tenants primaires', $primaryTenants],
                ['Tenants SaaS', $saasTenants],
                ['Tenants mono-compte', $singleTenants],
            ]
        );

        // Fonctionnalités disponibles
        $features = $this->licenceModeService->getAvailableFeatures();
        $this->info('\n🎯 Fonctionnalités disponibles:');
        foreach ($features as $feature => $enabled) {
            $status = $enabled ? '✅' : '❌';
            $this->line("  {$status} {$feature}");
        }

        // Limites
        $limits = $this->licenceModeService->getLimits();
        $this->info('\n📏 Limites actuelles:');
        foreach ($limits as $item => $limit) {
            $limitText = $limit === null ? 'Illimité' : $limit;
            $this->line("  • {$item}: {$limitText}");
        }

        return 0;
    }

    /**
     * Rafraîchit le système
     */
    protected function refreshSystem(): int
    {
        $this->info('🔄 Rafraîchissement du système...');

        try {
            $this->licenceModeService->refreshMode();
            $this->info('✅ Système rafraîchi avec succès!');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du rafraîchissement: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Migre les données existantes vers le nouveau système
     */
    protected function migrateExistingData(): int
    {
        $this->info('🔄 Migration des données existantes...');

        try {
            DB::transaction(function () {
                // Mettre à jour les licences existantes sans fonctionnalités
                $licencesWithoutFeatures = SerialKey::whereNull('features')->get();
                
                foreach ($licencesWithoutFeatures as $licence) {
                    $features = SerialKey::getDefaultFeatures($licence->licence_type, $licence->is_saas_enabled ?? false);
                    $limits = SerialKey::getDefaultLimits($licence->licence_type, $licence->is_saas_enabled ?? false);
                    
                    $licence->update([
                        'features' => $features,
                        'limits' => $limits,
                        'is_saas_enabled' => $licence->licence_type === 'multi',
                    ]);
                    
                    $this->line("✅ Licence {$licence->serial_key} mise à jour");
                }

                // Mettre à jour les tenants existants sans mode de licence
                $tenantsWithoutMode = Tenant::whereNull('licence_mode')->get();
                
                foreach ($tenantsWithoutMode as $tenant) {
                    // Déterminer le mode basé sur l'existence d'autres tenants
                    $isOnlyTenant = Tenant::count() === 1;
                    $mode = $isOnlyTenant ? 'single' : 'saas';
                    
                    $tenant->update([
                        'licence_mode' => $mode,
                        'is_primary' => $isOnlyTenant,
                        'usage_stats' => [
                            'projects_count' => 0,
                            'clients_count' => $tenant->clients()->count(),
                            'api_calls_this_month' => 0,
                            'storage_used_mb' => 0,
                        ],
                    ]);
                    
                    $this->line("✅ Tenant {$tenant->name} mis à jour (mode: {$mode})");
                }
            });

            $this->info('✅ Migration des données terminée avec succès!');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la migration: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Crée une nouvelle licence
     */
    protected function createLicence(): int
    {
        $type = $this->option('type') ?? $this->choice('Type de licence', ['single', 'saas'], 'single');
        $projectId = $this->option('project') ?? $this->askForProject();
        
        if (!$projectId) {
            $this->error('Aucun projet spécifié ou trouvé.');
            return 1;
        }

        try {
            $isSaas = $type === 'saas';
            
            // Fonctionnalités personnalisées ou par défaut
            $features = $this->option('features') 
                ? json_decode($this->option('features'), true)
                : SerialKey::getDefaultFeatures($type === 'single' ? 'single' : 'multi', $isSaas);
            
            // Limites personnalisées ou par défaut
            $limits = $this->option('limits')
                ? json_decode($this->option('limits'), true)
                : SerialKey::getDefaultLimits($type === 'single' ? 'single' : 'multi', $isSaas);

            $licence = SerialKey::create([
                'serial_key' => strtoupper($type) . '-' . strtoupper(substr(md5(time() . rand()), 0, 12)),
                'status' => 'active',
                'project_id' => $projectId,
                'licence_type' => $type === 'single' ? 'single' : 'multi',
                'is_saas_enabled' => $isSaas,
                'max_tenants' => $isSaas ? 100 : 1,
                'max_clients_per_tenant' => $isSaas ? 1000 : 1,
                'max_projects' => $isSaas ? null : 10,
                'features' => $features,
                'limits' => $limits,
                'billing_cycle' => $isSaas ? 'monthly' : 'lifetime',
                'price' => $isSaas ? 99.99 : null,
                'currency' => 'EUR',
                'expires_at' => $isSaas ? Carbon::now()->addYear() : null,
            ]);

            $this->info("✅ Licence créée avec succès!");
            $this->info("Clé de licence: {$licence->serial_key}");
            $this->info("Type: {$type}");
            $this->info("Mode SaaS: " . ($isSaas ? 'Oui' : 'Non'));
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la création de la licence: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Demande à l'utilisateur de choisir un projet
     */
    protected function askForProject(): ?int
    {
        $projects = Project::all();
        
        if ($projects->isEmpty()) {
            $this->warn('Aucun projet trouvé. Création d\'un projet par défaut...');
            $project = Project::create([
                'name' => 'Default Project',
                'description' => 'Projet par défaut créé automatiquement',
                'status' => 'active',
            ]);
            return $project->id;
        }

        $choices = $projects->pluck('name', 'id')->toArray();
        $projectId = $this->choice('Choisissez un projet', $choices);
        
        return array_search($projectId, $choices);
    }
}