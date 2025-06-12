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
            // Ajout de la relation avec la licence
            $table->foreignId('licence_id')->nullable()->after('id')->constrained('serial_keys')->onDelete('set null');
            
            // Ajout du champ description
            $table->text('description')->nullable()->after('name');
            
            // Remplacement du champ subscription_plan par subscription_id
            if (Schema::hasColumn('tenants', 'subscription_plan')) {
                $table->dropColumn('subscription_plan');
            }
            $table->string('subscription_id')->nullable()->after('settings');
            
            // Ajout d'index pour améliorer les performances
            $table->index('licence_id');
            $table->index('status');
            $table->index('subscription_status');
            $table->index(['subscription_ends_at', 'subscription_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Suppression des index
            $table->dropIndex(['licence_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['subscription_status']);
            $table->dropIndex(['subscription_ends_at', 'subscription_status']);
            
            // Suppression des champs ajoutés
            $table->dropColumn('licence_id');
            $table->dropColumn('description');
            $table->dropColumn('subscription_id');
            
            // Restauration du champ subscription_plan
            $table->string('subscription_plan')->nullable()->after('settings');
        });
    }
};
