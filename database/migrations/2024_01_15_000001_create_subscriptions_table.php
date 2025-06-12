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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Informations du plan
            $table->string('plan_id'); // basic, premium, enterprise
            $table->string('plan_name');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->string('interval')->default('month'); // month, year
            
            // Statut de l'abonnement
            $table->enum('status', [
                'trial',
                'active', 
                'canceled',
                'suspended',
                'past_due',
                'expired'
            ])->default('trial');
            
            // Fournisseur de paiement
            $table->enum('provider', ['stripe', 'paypal']);
            $table->string('provider_subscription_id')->nullable();
            $table->string('provider_customer_id')->nullable();
            
            // Dates importantes
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            
            // Configuration
            $table->boolean('auto_renew')->default(true);
            $table->json('metadata')->nullable(); // Données supplémentaires du fournisseur
            
            $table->timestamps();
            
            // Index
            $table->index(['tenant_id', 'status']);
            $table->index(['provider', 'provider_subscription_id']);
            $table->index('status');
            $table->index('next_billing_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};