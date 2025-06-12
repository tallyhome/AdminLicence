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
            // Index pour les colonnes fréquemment utilisées dans les recherches et filtres
            $table->index('serial_key');
            $table->index('status');
            $table->index('project_id');
            $table->index('domain');
            $table->index('ip_address');
            $table->index('expires_at');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serial_keys', function (Blueprint $table) {
            // Supprimer les index ajoutés
            $table->dropIndex(['serial_key']);
            $table->dropIndex(['status']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['domain']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });
    }
};
