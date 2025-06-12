<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 5, 100);
        $amount = round($quantity * $unitPrice, 2);
        $type = $this->faker->randomElement(['subscription', 'one_time', 'usage', 'discount', 'tax']);
        
        // Ajuster les valeurs selon le type
        if ($type === 'discount') {
            $amount = -abs($amount); // Les réductions sont négatives
        } elseif ($type === 'tax') {
            $amount = abs($amount); // Les taxes sont positives
            $unitPrice = abs($unitPrice);
        }

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->generateDescription($type),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $amount,
            'currency' => 'EUR',
            'type' => $type,
            'billing_period_start' => $type === 'subscription' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'billing_period_end' => $type === 'subscription' ? $this->faker->dateTimeBetween('now', '+1 month') : null,
            'provider_item_id' => $this->generateProviderItemId(),
            'metadata' => [
                'created_via' => 'factory',
                'test_data' => true,
                'item_category' => $this->faker->randomElement(['software', 'service', 'support', 'addon'])
            ]
        ];
    }

    /**
     * Indicate that the item is a subscription item.
     */
    public function subscription(): static
    {
        return $this->state(function (array $attributes) {
            $start = $this->faker->dateTimeBetween('-1 month', 'now');
            $end = (clone $start)->modify('+1 month');
            
            return [
                'type' => 'subscription',
                'description' => 'Abonnement ' . $this->faker->randomElement(['Basic', 'Premium', 'Enterprise']) . ' - ' . $this->faker->monthName,
                'quantity' => 1,
                'billing_period_start' => $start,
                'billing_period_end' => $end
            ];
        });
    }

    /**
     * Indicate that the item is a one-time charge.
     */
    public function oneTime(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'one_time',
                'description' => $this->faker->randomElement([
                    'Frais de configuration',
                    'Formation utilisateur',
                    'Support premium',
                    'Migration de données',
                    'Personnalisation'
                ]),
                'quantity' => $this->faker->numberBetween(1, 5),
                'billing_period_start' => null,
                'billing_period_end' => null
            ];
        });
    }

    /**
     * Indicate that the item is a usage-based charge.
     */
    public function usage(): static
    {
        return $this->state(function (array $attributes) {
            $usage = $this->faker->numberBetween(100, 10000);
            $unitPrice = $this->faker->randomFloat(4, 0.001, 0.1);
            
            return [
                'type' => 'usage',
                'description' => 'Utilisation API - ' . number_format($usage) . ' requêtes',
                'quantity' => $usage,
                'unit_price' => $unitPrice,
                'amount' => round($usage * $unitPrice, 2),
                'billing_period_start' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'billing_period_end' => $this->faker->dateTimeBetween('now', '+1 week')
            ];
        });
    }

    /**
     * Indicate that the item is a discount.
     */
    public function discount(): static
    {
        return $this->state(function (array $attributes) {
            $discountAmount = $this->faker->randomFloat(2, 5, 50);
            
            return [
                'type' => 'discount',
                'description' => $this->faker->randomElement([
                    'Réduction nouveau client',
                    'Code promo WELCOME20',
                    'Remise fidélité',
                    'Réduction volume',
                    'Crédit de service'
                ]),
                'quantity' => 1,
                'unit_price' => -$discountAmount,
                'amount' => -$discountAmount,
                'billing_period_start' => null,
                'billing_period_end' => null
            ];
        });
    }

    /**
     * Indicate that the item is a tax.
     */
    public function tax(): static
    {
        return $this->state(function (array $attributes) {
            $taxRate = $this->faker->randomElement([20, 19.6, 21, 25]); // Taux TVA européens
            $baseAmount = $this->faker->randomFloat(2, 10, 200);
            $taxAmount = round($baseAmount * ($taxRate / 100), 2);
            
            return [
                'type' => 'tax',
                'description' => 'TVA (' . $taxRate . '%)',
                'quantity' => 1,
                'unit_price' => $taxAmount,
                'amount' => $taxAmount,
                'billing_period_start' => null,
                'billing_period_end' => null,
                'metadata' => [
                    'tax_rate' => $taxRate,
                    'tax_type' => 'VAT',
                    'base_amount' => $baseAmount
                ]
            ];
        });
    }

    /**
     * Indicate that the item has a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(function (array $attributes) use ($amount) {
            $quantity = $attributes['quantity'] ?? 1;
            $unitPrice = $quantity > 0 ? round($amount / $quantity, 2) : $amount;
            
            return [
                'unit_price' => $unitPrice,
                'amount' => $amount
            ];
        });
    }

    /**
     * Indicate that the item has a specific quantity.
     */
    public function quantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $unitPrice = $attributes['unit_price'] ?? $this->faker->randomFloat(2, 5, 100);
            $amount = round($quantity * $unitPrice, 2);
            
            return [
                'quantity' => $quantity,
                'amount' => $amount
            ];
        });
    }

    /**
     * Indicate that the item is for a specific billing period.
     */
    public function billingPeriod(\DateTime $start, \DateTime $end): static
    {
        return $this->state(function (array $attributes) use ($start, $end) {
            return [
                'billing_period_start' => $start,
                'billing_period_end' => $end
            ];
        });
    }

    /**
     * Indicate that the item is for the current month.
     */
    public function currentMonth(): static
    {
        return $this->state(function (array $attributes) {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            
            return [
                'billing_period_start' => $start,
                'billing_period_end' => $end
            ];
        });
    }

    /**
     * Generate a description based on the item type.
     */
    protected function generateDescription(string $type): string
    {
        return match($type) {
            'subscription' => 'Abonnement ' . $this->faker->randomElement(['Basic', 'Premium', 'Enterprise']) . ' - ' . $this->faker->monthName,
            'one_time' => $this->faker->randomElement([
                'Frais de configuration',
                'Formation utilisateur',
                'Support premium',
                'Migration de données',
                'Personnalisation',
                'Consultation expert'
            ]),
            'usage' => 'Utilisation ' . $this->faker->randomElement([
                'API - requêtes supplémentaires',
                'Stockage - Go supplémentaires',
                'Bande passante - transfert de données',
                'Utilisateurs - licences supplémentaires'
            ]),
            'discount' => $this->faker->randomElement([
                'Réduction nouveau client',
                'Code promo',
                'Remise fidélité',
                'Réduction volume',
                'Crédit de service'
            ]),
            'tax' => 'TVA (' . $this->faker->randomElement([20, 19.6, 21, 25]) . '%)',
            default => $this->faker->sentence(3)
        };
    }

    /**
     * Generate a provider-specific item ID.
     */
    protected function generateProviderItemId(): string
    {
        $providers = ['stripe', 'paypal'];
        $provider = $this->faker->randomElement($providers);
        
        return match($provider) {
            'stripe' => 'ii_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'paypal' => 'ITEM-' . $this->faker->regexify('[A-Z0-9]{13}'),
            default => $this->faker->uuid
        };
    }
}