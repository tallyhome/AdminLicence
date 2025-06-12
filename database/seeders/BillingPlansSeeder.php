<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuration des plans de facturation
        $plans = [
            'basic' => [
                'name' => 'Plan Basic',
                'description' => 'Plan de base pour les petites entreprises',
                'price_monthly' => 29.99,
                'price_yearly' => 299.99,
                'currency' => 'EUR',
                'features' => [
                    'Jusqu\'à 5 projets',
                    '100 clés de licence',
                    '10 clés API',
                    'Support email',
                    'Tableau de bord basique'
                ],
                'limits' => [
                    'projects' => 5,
                    'license_keys' => 100,
                    'api_keys' => 10,
                    'users' => 3,
                    'storage_gb' => 1
                ],
                'stripe_price_id_monthly' => 'price_basic_monthly',
                'stripe_price_id_yearly' => 'price_basic_yearly',
                'paypal_plan_id_monthly' => 'P-basic-monthly',
                'paypal_plan_id_yearly' => 'P-basic-yearly',
                'trial_days' => 14,
                'popular' => false,
                'active' => true
            ],
            'premium' => [
                'name' => 'Plan Premium',
                'description' => 'Plan avancé pour les entreprises en croissance',
                'price_monthly' => 79.99,
                'price_yearly' => 799.99,
                'currency' => 'EUR',
                'features' => [
                    'Jusqu\'à 25 projets',
                    '1000 clés de licence',
                    '50 clés API',
                    'Support prioritaire',
                    'Tableau de bord avancé',
                    'Rapports détaillés',
                    'Intégrations webhook'
                ],
                'limits' => [
                    'projects' => 25,
                    'license_keys' => 1000,
                    'api_keys' => 50,
                    'users' => 10,
                    'storage_gb' => 10
                ],
                'stripe_price_id_monthly' => 'price_premium_monthly',
                'stripe_price_id_yearly' => 'price_premium_yearly',
                'paypal_plan_id_monthly' => 'P-premium-monthly',
                'paypal_plan_id_yearly' => 'P-premium-yearly',
                'trial_days' => 14,
                'popular' => true,
                'active' => true
            ],
            'enterprise' => [
                'name' => 'Plan Enterprise',
                'description' => 'Plan complet pour les grandes entreprises',
                'price_monthly' => 199.99,
                'price_yearly' => 1999.99,
                'currency' => 'EUR',
                'features' => [
                    'Projets illimités',
                    'Clés de licence illimitées',
                    'Clés API illimitées',
                    'Support dédié 24/7',
                    'Tableau de bord personnalisé',
                    'Rapports avancés',
                    'Intégrations complètes',
                    'SLA garanti',
                    'Formation incluse'
                ],
                'limits' => [
                    'projects' => -1, // -1 = illimité
                    'license_keys' => -1,
                    'api_keys' => -1,
                    'users' => -1,
                    'storage_gb' => -1
                ],
                'stripe_price_id_monthly' => 'price_enterprise_monthly',
                'stripe_price_id_yearly' => 'price_enterprise_yearly',
                'paypal_plan_id_monthly' => 'P-enterprise-monthly',
                'paypal_plan_id_yearly' => 'P-enterprise-yearly',
                'trial_days' => 30,
                'popular' => false,
                'active' => true
            ]
        ];

        // Insérer ou mettre à jour la configuration dans la table settings
        foreach ($plans as $planId => $planData) {
            DB::table('settings')->updateOrInsert(
                ['key' => "billing.plans.{$planId}"],
                [
                    'value' => json_encode($planData),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // Configuration générale de facturation
        $billingSettings = [
            'billing.enabled' => true,
            'billing.mode' => 'saas', // saas ou license
            'billing.currency' => 'EUR',
            'billing.tax_rate' => 20.0, // TVA 20%
            'billing.grace_period_days' => 7,
            'billing.trial_days_default' => 14,
            'billing.stripe.enabled' => true,
            'billing.paypal.enabled' => true,
            'billing.invoice_prefix' => 'INV-',
            'billing.auto_suspend_after_days' => 3,
            'billing.auto_delete_after_days' => 30
        ];

        foreach ($billingSettings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Plans de facturation créés avec succès!');
    }
}