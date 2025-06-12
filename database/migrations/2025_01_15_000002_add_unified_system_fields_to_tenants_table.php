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
        Schema::table('tenants', function (Blueprint $table) {
            // Champs pour le système unifié
            $table->foreignId('serial_key_id')->nullable()->constrained()->onDelete('set null')->comment('Licence associée à ce tenant');
            $table->boolean('is_primary')->default(false)->comment('Indique si c\'est le tenant principal (mode mono-compte)');
            $table->json('licence_features')->nullable()->comment('Fonctionnalités héritées de la licence');
            $table->json('usage_stats')->nullable()->comment('Statistiques d\'utilisation du tenant');
            $table->integer('max_clients')->nullable()->comment('Nombre maximum de clients autorisés');
            $table->integer('max_projects')->nullable()->comment('Nombre maximum de projets autorisés');
            $table->string('licence_mode')->default('single')->comment('Mode de licence: single ou saas');
            $table->timestamp('licence_expires_at')->nullable()->comment('Date d\'expiration de la licence');
            
            // Index pour optimiser les requêtes
            $table->index('is_primary');
            $table->index('licence_mode');
            $table->index(['serial_key_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['serial_key_id']);
            $table->dropIndex(['tenants_is_primary_index']);
            $table->dropIndex(['tenants_licence_mode_index']);
            $table->dropIndex(['tenants_serial_key_id_status_index']);
            $table->dropColumn([
                'serial_key_id',
                'is_primary',
                'licence_features',
                'usage_stats',
                'max_clients',
                'max_projects',
                'licence_mode',
                'licence_expires_at'
            ]);
        });
    }
};