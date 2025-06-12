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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            
            // Informations de l'élément
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            
            // Type d'élément
            $table->enum('type', [
                'subscription',
                'one_time',
                'usage',
                'discount',
                'tax'
            ])->default('subscription');
            
            // Période de facturation (pour les abonnements)
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            
            // Fournisseur
            $table->string('provider_item_id')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index('invoice_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};