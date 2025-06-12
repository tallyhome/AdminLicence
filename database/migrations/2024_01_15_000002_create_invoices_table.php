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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            
            // Informations de la facture
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            
            // Statut de la facture
            $table->enum('status', [
                'draft',
                'open',
                'paid',
                'void',
                'uncollectible'
            ])->default('open');
            
            // Fournisseur de paiement
            $table->enum('provider', ['stripe', 'paypal']);
            $table->string('provider_invoice_id')->nullable();
            $table->string('provider_payment_intent_id')->nullable();
            $table->string('provider_charge_id')->nullable();
            
            // Dates importantes
            $table->timestamp('issued_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            
            // Informations de paiement
            $table->string('payment_method')->nullable(); // card, bank_transfer, paypal, etc.
            $table->string('payment_method_details')->nullable(); // **** 4242, etc.
            
            // URLs
            $table->string('hosted_invoice_url')->nullable();
            $table->string('invoice_pdf_url')->nullable();
            
            // Métadonnées
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Index
            $table->index(['tenant_id', 'status']);
            $table->index(['subscription_id', 'status']);
            $table->index(['provider', 'provider_invoice_id']);
            $table->index('status');
            $table->index('issued_at');
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};