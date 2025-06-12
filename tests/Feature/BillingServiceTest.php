<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\StripeService;
use App\Services\PaypalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $billingService;
    protected $stripeService;
    protected $paypalService;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock des services
        $this->stripeService = Mockery::mock(StripeService::class);
        $this->paypalService = Mockery::mock(PaypalService::class);
        
        $this->billingService = new BillingService(
            $this->stripeService,
            $this->paypalService
        );

        // Créer un tenant de test
        $this->tenant = Tenant::factory()->create([
            'billing_status' => 'trial',
            'trial_ends_at' => now()->addDays(14)
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_available_plans()
    {
        $plans = $this->billingService->getAvailablePlans();
        
        $this->assertIsArray($plans);
        $this->assertArrayHasKey('basic', $plans);
        $this->assertArrayHasKey('premium', $plans);
        $this->assertArrayHasKey('enterprise', $plans);
        
        // Vérifier la structure d'un plan
        $basicPlan = $plans['basic'];
        $this->assertArrayHasKey('name', $basicPlan);
        $this->assertArrayHasKey('price_monthly', $basicPlan);
        $this->assertArrayHasKey('limits', $basicPlan);
        $this->assertArrayHasKey('features', $basicPlan);
    }

    /** @test */
    public function it_can_get_plan_details()
    {
        $plan = $this->billingService->getPlan('basic');
        
        $this->assertIsArray($plan);
        $this->assertEquals('Plan Basic', $plan['name']);
        $this->assertArrayHasKey('limits', $plan);
        $this->assertArrayHasKey('features', $plan);
    }

    /** @test */
    public function it_returns_null_for_invalid_plan()
    {
        $plan = $this->billingService->getPlan('invalid_plan');
        
        $this->assertNull($plan);
    }

    /** @test */
    public function it_can_create_stripe_subscription()
    {
        // Mock Stripe service
        $this->stripeService->shouldReceive('createCustomer')
            ->once()
            ->with($this->tenant)
            ->andReturn(['id' => 'cus_test123']);
            
        $this->stripeService->shouldReceive('createSubscription')
            ->once()
            ->andReturn([
                'id' => 'sub_test123',
                'status' => 'active',
                'current_period_end' => time() + (30 * 24 * 60 * 60)
            ]);

        $subscription = $this->billingService->createSubscription(
            $this->tenant,
            'basic',
            'stripe',
            'month'
        );

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals('stripe', $subscription->provider);
        $this->assertEquals('basic', $subscription->plan_id);
        $this->assertEquals('sub_test123', $subscription->provider_subscription_id);
    }

    /** @test */
    public function it_can_create_paypal_subscription()
    {
        // Mock PayPal service
        $this->paypalService->shouldReceive('createSubscription')
            ->once()
            ->andReturn([
                'id' => 'I-paypal123',
                'status' => 'ACTIVE'
            ]);

        $subscription = $this->billingService->createSubscription(
            $this->tenant,
            'premium',
            'paypal',
            'year'
        );

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals('paypal', $subscription->provider);
        $this->assertEquals('premium', $subscription->plan_id);
        $this->assertEquals('I-paypal123', $subscription->provider_subscription_id);
    }

    /** @test */
    public function it_can_cancel_subscription()
    {
        // Créer un abonnement
        $subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'provider' => 'stripe',
            'provider_subscription_id' => 'sub_test123',
            'status' => 'active'
        ]);

        // Mock Stripe service
        $this->stripeService->shouldReceive('cancelSubscription')
            ->once()
            ->with('sub_test123')
            ->andReturn(['status' => 'canceled']);

        $result = $this->billingService->cancelSubscription($subscription);

        $this->assertTrue($result);
        $this->assertEquals('canceled', $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->canceled_at);
    }

    /** @test */
    public function it_can_suspend_subscription()
    {
        // Créer un abonnement
        $subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'provider' => 'paypal',
            'provider_subscription_id' => 'I-paypal123',
            'status' => 'active'
        ]);

        // Mock PayPal service
        $this->paypalService->shouldReceive('suspendSubscription')
            ->once()
            ->with('I-paypal123')
            ->andReturn(['status' => 'SUSPENDED']);

        $result = $this->billingService->suspendSubscription($subscription);

        $this->assertTrue($result);
        $this->assertEquals('suspended', $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->suspended_at);
    }

    /** @test */
    public function it_can_reactivate_subscription()
    {
        // Créer un abonnement suspendu
        $subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'provider' => 'paypal',
            'provider_subscription_id' => 'I-paypal123',
            'status' => 'suspended',
            'suspended_at' => now()->subDays(5)
        ]);

        // Mock PayPal service
        $this->paypalService->shouldReceive('reactivateSubscription')
            ->once()
            ->with('I-paypal123')
            ->andReturn(['status' => 'ACTIVE']);

        $result = $this->billingService->reactivateSubscription($subscription);

        $this->assertTrue($result);
        $this->assertEquals('active', $subscription->fresh()->status);
        $this->assertNull($subscription->fresh()->suspended_at);
    }

    /** @test */
    public function it_can_check_tenant_limits()
    {
        // Mettre à jour le tenant avec un plan
        $this->tenant->update([
            'current_plan' => 'basic',
            'plan_limits' => [
                'projects' => 5,
                'license_keys' => 100,
                'api_keys' => 10
            ],
            'current_usage' => [
                'projects' => 3,
                'license_keys' => 50,
                'api_keys' => 5
            ]
        ]);

        // Test limite non atteinte
        $canCreateProject = $this->billingService->checkLimit($this->tenant, 'projects');
        $this->assertTrue($canCreateProject);

        // Test limite atteinte
        $this->tenant->update([
            'current_usage' => [
                'projects' => 5,
                'license_keys' => 50,
                'api_keys' => 5
            ]
        ]);

        $canCreateProject = $this->billingService->checkLimit($this->tenant, 'projects');
        $this->assertFalse($canCreateProject);
    }

    /** @test */
    public function it_can_get_tenant_usage_percentage()
    {
        $this->tenant->update([
            'current_plan' => 'basic',
            'plan_limits' => [
                'projects' => 5,
                'license_keys' => 100
            ],
            'current_usage' => [
                'projects' => 3,
                'license_keys' => 75
            ]
        ]);

        $usage = $this->billingService->getTenantUsage($this->tenant);

        $this->assertEquals(60, $usage['projects']['percentage']); // 3/5 * 100
        $this->assertEquals(75, $usage['license_keys']['percentage']); // 75/100 * 100
    }

    /** @test */
    public function it_handles_unlimited_limits()
    {
        $this->tenant->update([
            'current_plan' => 'enterprise',
            'plan_limits' => [
                'projects' => -1, // Illimité
                'license_keys' => -1
            ],
            'current_usage' => [
                'projects' => 1000,
                'license_keys' => 5000
            ]
        ]);

        $canCreateProject = $this->billingService->checkLimit($this->tenant, 'projects');
        $this->assertTrue($canCreateProject);

        $usage = $this->billingService->getTenantUsage($this->tenant);
        $this->assertEquals(0, $usage['projects']['percentage']); // Illimité = 0%
    }

    /** @test */
    public function it_can_create_invoice()
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'amount' => 29.99,
            'currency' => 'EUR'
        ]);

        $invoice = $this->billingService->createInvoice(
            $subscription,
            29.99,
            'EUR',
            'paid'
        );

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($this->tenant->id, $invoice->tenant_id);
        $this->assertEquals($subscription->id, $invoice->subscription_id);
        $this->assertEquals(29.99, $invoice->amount);
        $this->assertEquals('paid', $invoice->status);
    }

    /** @test */
    public function it_generates_unique_invoice_numbers()
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);

        $invoice1 = $this->billingService->createInvoice($subscription, 29.99, 'EUR', 'paid');
        $invoice2 = $this->billingService->createInvoice($subscription, 79.99, 'EUR', 'paid');

        $this->assertNotEquals($invoice1->invoice_number, $invoice2->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice1->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice2->invoice_number);
    }

    /** @test */
    public function it_can_get_tenant_invoices()
    {
        $subscription = Subscription::factory()->create([
            'tenant_id' => $this->tenant->id
        ]);

        // Créer quelques factures
        Invoice::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'subscription_id' => $subscription->id
        ]);

        $invoices = $this->billingService->getTenantInvoices($this->tenant);

        $this->assertCount(3, $invoices);
        $this->assertInstanceOf(Invoice::class, $invoices->first());
    }

    /** @test */
    public function it_can_calculate_plan_price_with_interval()
    {
        $monthlyPrice = $this->billingService->calculatePlanPrice('basic', 'month');
        $yearlyPrice = $this->billingService->calculatePlanPrice('basic', 'year');

        $this->assertEquals(29.99, $monthlyPrice);
        $this->assertEquals(299.99, $yearlyPrice);
    }
}