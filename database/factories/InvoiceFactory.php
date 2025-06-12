<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 10, 500);
        $taxRate = 20; // 20% TVA
        $taxAmount = round($amount * ($taxRate / 100), 2);
        $totalAmount = $amount + $taxAmount;
        
        $provider = $this->faker->randomElement(['stripe', 'paypal']);
        $status = $this->faker->randomElement(['draft', 'open', 'paid', 'void', 'uncollectible']);
        
        $issuedAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $dueAt = (clone $issuedAt)->modify('+30 days');
        
        // Si la facture est payée, définir une date de paiement
        $paidAt = null;
        if ($status === 'paid') {
            $paidAt = $this->faker->dateTimeBetween($issuedAt, $dueAt);
        }
        
        // Si la facture est annulée, définir une date d'annulation
        $voidedAt = null;
        if ($status === 'void') {
            $voidedAt = $this->faker->dateTimeBetween($issuedAt, 'now');
        }

        return [
            'tenant_id' => Tenant::factory(),
            'subscription_id' => Subscription::factory(),
            'invoice_number' => $this->generateInvoiceNumber(),
            'amount' => $amount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'currency' => 'EUR',
            'status' => $status,
            'provider' => $provider,
            'provider_invoice_id' => $this->generateProviderInvoiceId($provider),
            'provider_payment_intent_id' => $status === 'paid' ? $this->generateProviderPaymentIntentId($provider) : null,
            'provider_charge_id' => $status === 'paid' ? $this->generateProviderChargeId($provider) : null,
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'paid_at' => $paidAt,
            'voided_at' => $voidedAt,
            'payment_method' => $status === 'paid' ? $this->faker->randomElement(['card', 'bank_transfer', 'paypal']) : null,
            'payment_method_details' => $status === 'paid' ? $this->generatePaymentMethodDetails() : null,
            'hosted_invoice_url' => $this->faker->url,
            'invoice_pdf_url' => $this->faker->url . '/invoice.pdf',
            'metadata' => [
                'created_via' => 'factory',
                'test_data' => true,
                'billing_cycle' => $this->faker->randomElement(['monthly', 'yearly'])
            ],
            'description' => $this->faker->sentence()
        ];
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $issuedAt = $attributes['issued_at'] ?? $this->faker->dateTimeBetween('-3 months', '-1 month');
            $paidAt = $this->faker->dateTimeBetween($issuedAt, 'now');
            
            return [
                'status' => 'paid',
                'paid_at' => $paidAt,
                'payment_method' => $this->faker->randomElement(['card', 'bank_transfer', 'paypal']),
                'payment_method_details' => $this->generatePaymentMethodDetails(),
                'provider_payment_intent_id' => $this->generateProviderPaymentIntentId($attributes['provider'] ?? 'stripe'),
                'provider_charge_id' => $this->generateProviderChargeId($attributes['provider'] ?? 'stripe')
            ];
        });
    }

    /**
     * Indicate that the invoice is open (unpaid).
     */
    public function open(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'open',
                'paid_at' => null,
                'voided_at' => null,
                'payment_method' => null,
                'payment_method_details' => null,
                'provider_payment_intent_id' => null,
                'provider_charge_id' => null
            ];
        });
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            $issuedAt = $this->faker->dateTimeBetween('-3 months', '-2 months');
            $dueAt = (clone $issuedAt)->modify('+30 days');
            
            return [
                'status' => 'open',
                'issued_at' => $issuedAt,
                'due_at' => $dueAt, // Date dépassée
                'paid_at' => null,
                'voided_at' => null
            ];
        });
    }

    /**
     * Indicate that the invoice is void.
     */
    public function void(): static
    {
        return $this->state(function (array $attributes) {
            $voidedAt = $this->faker->dateTimeBetween('-1 month', 'now');
            
            return [
                'status' => 'void',
                'voided_at' => $voidedAt,
                'paid_at' => null,
                'payment_method' => null,
                'payment_method_details' => null
            ];
        });
    }

    /**
     * Indicate that the invoice is uncollectible.
     */
    public function uncollectible(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'uncollectible',
                'paid_at' => null,
                'payment_method' => null,
                'payment_method_details' => null
            ];
        });
    }

    /**
     * Indicate that the invoice uses Stripe.
     */
    public function stripe(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'stripe',
                'provider_invoice_id' => $this->generateProviderInvoiceId('stripe')
            ];
        });
    }

    /**
     * Indicate that the invoice uses PayPal.
     */
    public function paypal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'paypal',
                'provider_invoice_id' => $this->generateProviderInvoiceId('paypal')
            ];
        });
    }

    /**
     * Indicate that the invoice is for a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $taxRate = 20; // 20% TVA
            $taxAmount = round($amount * ($taxRate / 100), 2);
            $totalAmount = $amount + $taxAmount;
            
            return [
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount
            ];
        });
    }

    /**
     * Indicate that the invoice is recent.
     */
    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            $issuedAt = $this->faker->dateTimeBetween('-1 month', 'now');
            $dueAt = (clone $issuedAt)->modify('+30 days');
            
            return [
                'issued_at' => $issuedAt,
                'due_at' => $dueAt
            ];
        });
    }

    /**
     * Generate a unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        $prefix = config('billing.invoice_prefix', 'INV-');
        $year = date('Y');
        $month = date('m');
        $random = $this->faker->unique()->numberBetween(1000, 9999);
        
        return $prefix . $year . $month . $random;
    }

    /**
     * Generate a provider-specific invoice ID.
     */
    protected function generateProviderInvoiceId(string $provider): string
    {
        return match($provider) {
            'stripe' => 'in_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'paypal' => 'INV2-' . $this->faker->regexify('[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}'),
            default => $this->faker->uuid
        };
    }

    /**
     * Generate a provider-specific payment intent ID.
     */
    protected function generateProviderPaymentIntentId(string $provider): string
    {
        return match($provider) {
            'stripe' => 'pi_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'paypal' => 'PAYID-' . $this->faker->regexify('[A-Z0-9]{13}'),
            default => $this->faker->uuid
        };
    }

    /**
     * Generate a provider-specific charge ID.
     */
    protected function generateProviderChargeId(string $provider): string
    {
        return match($provider) {
            'stripe' => 'ch_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'paypal' => 'PAY-' . $this->faker->regexify('[A-Z0-9]{17}'),
            default => $this->faker->uuid
        };
    }

    /**
     * Generate payment method details.
     */
    protected function generatePaymentMethodDetails(): string
    {
        $methods = [
            '**** **** **** ' . $this->faker->numberBetween(1000, 9999), // Carte
            'PayPal (' . $this->faker->email . ')', // PayPal
            'Virement bancaire', // Virement
            'SEPA Direct Debit' // Prélèvement SEPA
        ];
        
        return $this->faker->randomElement($methods);
    }
}