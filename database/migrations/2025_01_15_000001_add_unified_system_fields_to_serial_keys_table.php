<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('serial_keys', function (Blueprint $table) {
            // Champs pour le système unifié mono-compte/SaaS
            $table->json('features')->nullable()->comment('Fonctionnalités activées pour cette licence');
            $table->json('limits')->nullable()->comment('Limites spécifiques à cette licence (projets, utilisateurs, etc.)');
            $table->boolean('is_saas_enabled')->default(false)->comment('Indique si cette licence active le mode SaaS');
            $table->integer('max_tenants')->nullable()->comment('Nombre maximum de tenants autorisés (mode SaaS)');
            $table->integer('max_clients_per_tenant')->nullable()->comment('Nombre maximum de clients par tenant');
            $table->integer('max_projects')->nullable()->comment('Nombre maximum de projets autorisés');
            $table->string('billing_cycle')->nullable()->comment('Cycle de facturation (monthly, yearly, lifetime)');
            $table->decimal('price', 10, 2)->nullable()->comment('Prix de la licence');
            $table->string('currency', 3)->default('EUR')->comment('Devise de la licence');
            $table->json('metadata')->nullable()->comment('Métadonnées additionnelles de la licence');
            
            // Index pour optimiser les requêtes
            $table->index('is_saas_enabled');
            $table->index(['licence_type', 'is_saas_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serial_keys', function (Blueprint $table) {
            $table->dropIndex(['serial_keys_is_saas_enabled_index']);
            $table->dropIndex(['serial_keys_licence_type_is_saas_enabled_index']);
            $table->dropColumn([
                'features',
                'limits',
                'is_saas_enabled',
                'max_tenants',
                'max_clients_per_tenant',
                'max_projects',
                'billing_cycle',
                'price',
                'currency',
                'metadata'
            ]);
        });
    }
};