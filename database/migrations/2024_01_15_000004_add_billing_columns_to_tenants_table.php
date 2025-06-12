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
            // Statut de facturation
            $table->enum('billing_status', [
                'active',
                'trial',
                'suspended',
                'expired',
                'canceled'
            ])->default('trial')->after('status');
            
            // Plan actuel
            $table->string('current_plan')->nullable()->after('billing_status');
            
            // Fournisseur de paiement préféré
            $table->enum('preferred_payment_provider', ['stripe', 'paypal'])->nullable()->after('current_plan');
            
            // IDs des clients chez les fournisseurs
            $table->string('stripe_customer_id')->nullable()->after('preferred_payment_provider');
            $table->string('paypal_customer_id')->nullable()->after('stripe_customer_id');
            
            // Dates importantes
            $table->timestamp('trial_ends_at')->nullable()->after('paypal_customer_id');
            $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            $table->timestamp('grace_period_ends_at')->nullable()->after('subscription_ends_at');
            
            // Utilisation actuelle
            $table->json('current_usage')->nullable()->after('grace_period_ends_at');
            
            // Limites du plan
            $table->json('plan_limits')->nullable()->after('current_usage');
            
            // Informations de facturation
            $table->string('billing_email')->nullable()->after('plan_limits');
            $table->json('billing_address')->nullable()->after('billing_email');
            $table->string('tax_id')->nullable()->after('billing_address');
            
            // Index
            $table->index('billing_status');
            $table->index('current_plan');
            $table->index('trial_ends_at');
            $table->index('subscription_ends_at');
            $table->index('stripe_customer_id');
            $table->index('paypal_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['billing_status']);
            $table->dropIndex(['current_plan']);
            $table->dropIndex(['trial_ends_at']);
            $table->dropIndex(['subscription_ends_at']);
            $table->dropIndex(['stripe_customer_id']);
            $table->dropIndex(['paypal_customer_id']);
            
            $table->dropColumn([
                'billing_status',
                'current_plan',
                'preferred_payment_provider',
                'stripe_customer_id',
                'paypal_customer_id',
                'trial_ends_at',
                'subscription_ends_at',
                'grace_period_ends_at',
                'current_usage',
                'plan_limits',
                'billing_email',
                'billing_address',
                'tax_id'
            ]);
        });
    }
};