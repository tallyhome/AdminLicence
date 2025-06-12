<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SerialKey;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnifiedSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Créer un projet par défaut si aucun n'existe
            $project = Project::firstOrCreate(
                ['name' => 'Default Project'],
                [
                    'description' => 'Projet par défaut pour le système unifié',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Créer une licence mono-compte par défaut
            $singleLicence = SerialKey::firstOrCreate(
                ['serial_key' => 'SINGLE-DEFAULT-' . strtoupper(substr(md5(time()), 0, 8))],
                [
                    'status' => 'active',
                    'project_id' => $project->id,
                    'licence_type' => 'single',
                    'max_accounts' => 1,
                    'is_saas_enabled' => false,
                    'max_tenants' => 1,
                    'max_clients_per_tenant' => 1,
                    'max_projects' => 10,
                    'billing_cycle' => 'lifetime',
                    'features' => [
                        'project_management' => true,
                        'api_access' => true,
                        'email_templates' => true,
                        'basic_support' => true,
                        'multi_tenant' => false,
                        'advanced_analytics' => false,
                        'priority_support' => false
                    ],
                    'limits' => [
                        'projects' => 10,
                        'api_calls_per_month' => 10000,
                        'email_templates' => 5,
                        'storage_gb' => 1
                    ],
                    'expires_at' => null, // Licence à vie
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Créer une licence SaaS par défaut
            $saasLicence = SerialKey::firstOrCreate(
                ['serial_key' => 'SAAS-DEFAULT-' . strtoupper(substr(md5(time() + 1), 0, 8))],
                [
                    'status' => 'active',
                    'project_id' => $project->id,
                    'licence_type' => 'multi',
                    'max_accounts' => null, // Illimité
                    'is_saas_enabled' => true,
                    'max_tenants' => 100,
                    'max_clients_per_tenant' => 1000,
                    'max_projects' => null, // Illimité
                    'billing_cycle' => 'monthly',
                    'price' => 99.99,
                    'currency' => 'EUR',
                    'features' => [
                        'project_management' => true,
                        'api_access' => true,
                        'email_templates' => true,
                        'basic_support' => true,
                        'multi_tenant' => true,
                        'advanced_analytics' => true,
                        'priority_support' => true,
                        'white_label' => true,
                        'custom_domains' => true
                    ],
                    'limits' => [
                        'projects' => null, // Illimité
                        'api_calls_per_month' => 1000000,
                        'email_templates' => null, // Illimité
                        'storage_gb' => 100
                    ],
                    'expires_at' => Carbon::now()->addYear(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Créer un tenant principal pour la licence mono-compte
            $primaryTenant = Tenant::firstOrCreate(
                ['domain' => 'primary.local'],
                [
                    'name' => 'Primary Tenant',
                    'status' => 'active',
                    'serial_key_id' => $singleLicence->id,
                    'is_primary' => true,
                    'licence_mode' => 'single',
                    'max_clients' => 1,
                    'max_projects' => 10,
                    'licence_features' => $singleLicence->features,
                    'licence_expires_at' => $singleLicence->expires_at,
                    'subscription_status' => 'active',
                    'settings' => [
                        'theme' => 'default',
                        'timezone' => 'Europe/Paris',
                        'language' => 'fr'
                    ],
                    'usage_stats' => [
                        'projects_count' => 0,
                        'clients_count' => 0,
                        'api_calls_this_month' => 0,
                        'storage_used_mb' => 0
                    ],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            // Créer un tenant de démonstration pour la licence SaaS
            $demoTenant = Tenant::firstOrCreate(
                ['domain' => 'demo.saas.local'],
                [
                    'name' => 'Demo SaaS Tenant',
                    'status' => 'active',
                    'serial_key_id' => $saasLicence->id,
                    'is_primary' => false,
                    'licence_mode' => 'saas',
                    'max_clients' => 1000,
                    'max_projects' => null,
                    'licence_features' => $saasLicence->features,
                    'licence_expires_at' => $saasLicence->expires_at,
                    'subscription_status' => 'active',
                    'subscription_ends_at' => Carbon::now()->addMonth(),
                    'settings' => [
                        'theme' => 'modern',
                        'timezone' => 'Europe/Paris',
                        'language' => 'fr',
                        'custom_branding' => true
                    ],
                    'usage_stats' => [
                        'projects_count' => 0,
                        'clients_count' => 0,
                        'api_calls_this_month' => 0,
                        'storage_used_mb' => 0
                    ],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );

            $this->command->info('Système unifié initialisé avec succès!');
            $this->command->info('Licence mono-compte: ' . $singleLicence->serial_key);
            $this->command->info('Licence SaaS: ' . $saasLicence->serial_key);
            $this->command->info('Tenant principal: ' . $primaryTenant->domain);
            $this->command->info('Tenant démo SaaS: ' . $demoTenant->domain);
        });
    }
}