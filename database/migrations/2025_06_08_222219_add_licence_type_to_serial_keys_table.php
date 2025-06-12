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
            // Ajouter le champ licence_type pour différencier les licences mono-compte et multi-comptes
            $table->string('licence_type', 20)->default('single')->comment('Type de licence: single (mono-compte) ou multi (multi-comptes)');
            
            // Ajouter un champ pour le nombre maximum de comptes autorisés (pour les licences multi-comptes)
            $table->integer('max_accounts')->nullable()->default(null)->comment('Nombre maximum de comptes autorisés pour les licences multi-comptes');
            
            // Ajouter un index sur le champ licence_type pour optimiser les requêtes
            $table->index('licence_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serial_keys', function (Blueprint $table) {
            // Supprimer l'index
            $table->dropIndex(['licence_type']);
            
            // Supprimer les colonnes ajoutées
            $table->dropColumn('licence_type');
            $table->dropColumn('max_accounts');
        });
    }
};
