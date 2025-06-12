<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plans = ['basic', 'premium', 'enterprise'];
        $planId = $this->faker->randomElement($plans);
        $provider = $this->faker->randomElement(['stripe', 'paypal']);
        $interval = $this->faker->randomElement(['month', 'year']);
        
        // Prix selon le plan et l'intervalle
        $prices = [
            'basic' => ['month' => 29.99, 'year' => 299.99],
            'premium' => ['month' => 79.99, 'year' => 799.99],
            'enterprise' => ['month' => 199.99, 'year' => 1999.99]
        ];
        
        $planNames = [
            'basic' => 'Plan Basic',
            'premium' => 'Plan Premium',
            'enterprise' => 'Plan Enterprise'
        ];
        
        $amount = $prices[$planId][$interval];
        $planName = $planNames[$planId];
        
        $startsAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $endsAt = (clone $startsAt)->modify('+1 ' . $interval);
        $nextBillingAt = $interval === 'month' 
            ? (clone $startsAt)->modify('+1 month')
            : (clone $startsAt)->modify('+1 year');

        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => $planId,
            'plan_name' => $planName,
            'amount' => $amount,
            'currency' => 'EUR',
            'interval' => $interval,
            'status' => $this->faker->randomElement(['trial', 'active', 'canceled', 'suspended', 'past_due']),
            'provider' => $provider,
            'provider_subscription_id' => $this->generateProviderSubscriptionId($provider),
            'provider_customer_id' => $this->generateProviderCustomerId($provider),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'next_billing_at' => $nextBillingAt,
            'auto_renew' => $this->faker->boolean(80), // 80% de chance d'être en auto-renouvellement
            'metadata' => [
                'created_via' => 'factory',
                'test_data' => true
            ]
        ];
    }

    /**
     * Indicate that the subscription is in trial.
     */
    public function trial(): static
    {
        return $this->state(function (array $attributes) {
            $trialStartsAt = $this->faker->dateTimeBetween('-1 week', 'now');
            $trialEndsAt = (clone $trialStartsAt)->modify('+14 days');
            
            return [
                'status' => 'trial',
                'trial_starts_at' => $trialStartsAt,
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => null,
                'ends_at' => null,
                'next_billing_at' => $trialEndsAt,
                'amount' => 0.00
            ];
        });
    }

    /**
     * Indicate that the subscription is active.
     */
    public function active(): static
    {
        return $this->state(function (array $attributes) {
            $startsAt = $this->faker->dateTimeBetween('-3 months', '-1 month');
            $interval = $attributes['interval'] ?? 'month';
            $endsAt = (clone $startsAt)->modify('+1 ' . $interval);
            $nextBillingAt = $interval === 'month' 
                ? (clone $startsAt)->modify('+1 month')
                : (clone $startsAt)->modify('+1 year');
            
            return [
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'next_billing_at' => $nextBillingAt,
                'trial_starts_at' => null,
                'trial_ends_at' => null,
                'canceled_at' => null,
                'suspended_at' => null
            ];
        });
    }

    /**
     * Indicate that the subscription is canceled.
     */
    public function canceled(): static
    {
        return $this->state(function (array $attributes) {
            $canceledAt = $this->faker->dateTimeBetween('-1 month', 'now');
            
            return [
                'status' => 'canceled',
                'canceled_at' => $canceledAt,
                'auto_renew' => false,
                'next_billing_at' => null
            ];
        });
    }

    /**
     * Indicate that the subscription is suspended.
     */
    public function suspended(): static
    {
        return $this->state(function (array $attributes) {
            $suspendedAt = $this->faker->dateTimeBetween('-2 weeks', 'now');
            
            return [
                'status' => 'suspended',
                'suspended_at' => $suspendedAt,
                'auto_renew' => false
            ];
        });
    }

    /**
     * Indicate that the subscription is past due.
     */
    public function pastDue(): static
    {
        return $this->state(function (array $attributes) {
            $nextBillingAt = $this->faker->dateTimeBetween('-1 week', '-1 day');
            
            return [
                'status' => 'past_due',
                'next_billing_at' => $nextBillingAt
            ];
        });
    }

    /**
     * Indicate that the subscription is expired.
     */
    public function expired(): static
    {
        return $this->state(function (array $attributes) {
            $endsAt = $this->faker->dateTimeBetween('-1 month', '-1 day');
            
            return [
                'status' => 'expired',
                'ends_at' => $endsAt,
                'next_billing_at' => null,
                'auto_renew' => false
            ];
        });
    }

    /**
     * Indicate that the subscription uses Stripe.
     */
    public function stripe(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'stripe',
                'provider_subscription_id' => $this->generateProviderSubscriptionId('stripe'),
                'provider_customer_id' => $this->generateProviderCustomerId('stripe')
            ];
        });
    }

    /**
     * Indicate that the subscription uses PayPal.
     */
    public function paypal(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => 'paypal',
                'provider_subscription_id' => $this->generateProviderSubscriptionId('paypal'),
                'provider_customer_id' => $this->generateProviderCustomerId('paypal')
            ];
        });
    }

    /**
     * Indicate that the subscription is for a specific plan.
     */
    public function plan(string $planId): static
    {
        return $this->state(function (array $attributes) use ($planId) {
            $planNames = [
                'basic' => 'Plan Basic',
                'premium' => 'Plan Premium',
                'enterprise' => 'Plan Enterprise'
            ];
            
            $prices = [
                'basic' => ['month' => 29.99, 'year' => 299.99],
                'premium' => ['month' => 79.99, 'year' => 799.99],
                'enterprise' => ['month' => 199.99, 'year' => 1999.99]
            ];
            
            $interval = $attributes['interval'] ?? 'month';
            
            return [
                'plan_id' => $planId,
                'plan_name' => $planNames[$planId] ?? 'Plan Custom',
                'amount' => $prices[$planId][$interval] ?? 0.00
            ];
        });
    }

    /**
     * Indicate that the subscription is monthly.
     */
    public function monthly(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'interval' => 'month'
            ];
        });
    }

    /**
     * Indicate that the subscription is yearly.
     */
    public function yearly(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'interval' => 'year'
            ];
        });
    }

    /**
     * Generate a provider-specific subscription ID.
     */
    protected function generateProviderSubscriptionId(string $provider): string
    {
        return match($provider) {
            'stripe' => 'sub_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'paypal' => 'I-' . $this->faker->regexify('[A-Z0-9]{13}'),
            default => $this->faker->uuid
        };
    }

    /**
     * Generate a provider-specific customer ID.
     */
    protected function generateProviderCustomerId(string $provider): string
    {
        return match($provider) {
            'stripe' => 'cus_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'paypal' => $this->faker->regexify('[A-Z0-9]{13}'),
            default => $this->faker->uuid
        };
    }
}