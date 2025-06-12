<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class BillingModelsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Charger les seeders de configuration
        $this->artisan('db:seed', ['--class' => 'BillingPlansSeeder']);
    }

    /** @test */
    public function it_can_create_a_subscription_with_all_fields()
    {
        $tenant = Tenant::factory()->create();
        
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => 'premium',
            'plan_name' => 'Premium',
            'amount' => 29.99,
            'currency' => 'EUR',
            'interval' => 'monthly',
            'status' => 'active',
            'provider' => 'stripe'
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'tenant_id' => $tenant->id,
            'plan_id' => 'premium',
            'status' => 'active',
            'provider' => 'stripe'
        ]);

        $this->assertEquals($tenant->id, $subscription->tenant_id);
        $this->assertEquals('premium', $subscription->plan_id);
        $this->assertEquals(29.99, $subscription->amount);
    }

    /** @test */
    public function subscription_belongs_to_tenant()
    {
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create(['tenant_id' => $tenant->id]);

        $this->assertInstanceOf(Tenant::class, $subscription->tenant);
        $this->assertEquals($tenant->id, $subscription->tenant->id);
    }

    /** @test */
    public function tenant_can_have_multiple_subscriptions()
    {
        $tenant = Tenant::factory()->create();
        
        $activeSubscription = Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
        $canceledSubscription = Subscription::factory()->canceled()->create(['tenant_id' => $tenant->id]);

        $this->assertCount(2, $tenant->subscriptions);
        $this->assertTrue($tenant->subscriptions->contains($activeSubscription));
        $this->assertTrue($tenant->subscriptions->contains($canceledSubscription));
    }

    /** @test */
    public function it_can_get_active_subscription_for_tenant()
    {
        $tenant = Tenant::factory()->create();
        
        $activeSubscription = Subscription::factory()->active()->create(['tenant_id' => $tenant->id]);
        $canceledSubscription = Subscription::factory()->canceled()->create(['tenant_id' => $tenant->id]);

        $currentSubscription = $tenant->activeSubscription;
        
        $this->assertNotNull($currentSubscription);
        $this->assertEquals($activeSubscription->id, $currentSubscription->id);
        $this->assertEquals('active', $currentSubscription->status);
    }

    /** @test */
    public function subscription_can_check_if_on_trial()
    {
        $trialSubscription = Subscription::factory()->trial()->create();
        $activeSubscription = Subscription::factory()->active()->create();

        $this->assertTrue($trialSubscription->onTrial());
        $this->assertFalse($activeSubscription->onTrial());
    }

    /** @test */
    public function subscription_can_check_if_active()
    {
        $activeSubscription = Subscription::factory()->active()->create();
        $canceledSubscription = Subscription::factory()->canceled()->create();
        $suspendedSubscription = Subscription::factory()->suspended()->create();

        $this->assertTrue($activeSubscription->isActive());
        $this->assertFalse($canceledSubscription->isActive());
        $this->assertFalse($suspendedSubscription->isActive());
    }

    /** @test */
    public function subscription_can_check_if_expired()
    {
        $expiredSubscription = Subscription::factory()->expired()->create();
        $activeSubscription = Subscription::factory()->active()->create();

        $this->assertTrue($expiredSubscription->isExpired());
        $this->assertFalse($activeSubscription->isExpired());
    }

    /** @test */
    public function it_can_create_an_invoice_with_all_fields()
    {
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create(['tenant_id' => $tenant->id]);
        
        $invoice = Invoice::factory()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'amount' => 100.00,
            'tax_amount' => 20.00,
            'total_amount' => 120.00,
            'currency' => 'EUR',
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'status' => 'paid'
        ]);

        $this->assertEquals(100.00, $invoice->amount);
        $this->assertEquals(120.00, $invoice->total_amount);
    }

    /** @test */
    public function invoice_belongs_to_tenant_and_subscription()
    {
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->create(['tenant_id' => $tenant->id]);
        $invoice = Invoice::factory()->create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id
        ]);

        $this->assertInstanceOf(Tenant::class, $invoice->tenant);
        $this->assertInstanceOf(Subscription::class, $invoice->subscription);
        $this->assertEquals($tenant->id, $invoice->tenant->id);
        $this->assertEquals($subscription->id, $invoice->subscription->id);
    }

    /** @test */
    public function subscription_can_have_multiple_invoices()
    {
        $subscription = Subscription::factory()->create();
        
        $invoice1 = Invoice::factory()->create(['subscription_id' => $subscription->id]);
        $invoice2 = Invoice::factory()->create(['subscription_id' => $subscription->id]);

        $this->assertCount(2, $subscription->invoices);
        $this->assertTrue($subscription->invoices->contains($invoice1));
        $this->assertTrue($subscription->invoices->contains($invoice2));
    }

    /** @test */
    public function invoice_can_check_if_paid()
    {
        $paidInvoice = Invoice::factory()->paid()->create();
        $openInvoice = Invoice::factory()->open()->create();

        $this->assertTrue($paidInvoice->isPaid());
        $this->assertFalse($openInvoice->isPaid());
    }

    /** @test */
    public function invoice_can_check_if_overdue()
    {
        $overdueInvoice = Invoice::factory()->overdue()->create();
        $recentInvoice = Invoice::factory()->recent()->create();

        $this->assertTrue($overdueInvoice->isOverdue());
        $this->assertFalse($recentInvoice->isOverdue());
    }

    /** @test */
    public function it_can_create_invoice_items_with_different_types()
    {
        $invoice = Invoice::factory()->create();
        
        $subscriptionItem = InvoiceItem::factory()->subscription()->create(['invoice_id' => $invoice->id]);
        $oneTimeItem = InvoiceItem::factory()->oneTime()->create(['invoice_id' => $invoice->id]);
        $discountItem = InvoiceItem::factory()->discount()->create(['invoice_id' => $invoice->id]);
        $taxItem = InvoiceItem::factory()->tax()->create(['invoice_id' => $invoice->id]);

        $this->assertEquals('subscription', $subscriptionItem->type);
        $this->assertEquals('one_time', $oneTimeItem->type);
        $this->assertEquals('discount', $discountItem->type);
        $this->assertEquals('tax', $taxItem->type);
        
        // Vérifier que les réductions sont négatives
        $this->assertLessThan(0, $discountItem->amount);
        
        // Vérifier que les taxes sont positives
        $this->assertGreaterThan(0, $taxItem->amount);
    }

    /** @test */
    public function invoice_items_belong_to_invoice()
    {
        $invoice = Invoice::factory()->create();
        $item = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertInstanceOf(Invoice::class, $item->invoice);
        $this->assertEquals($invoice->id, $item->invoice->id);
    }

    /** @test */
    public function invoice_can_have_multiple_items()
    {
        $invoice = Invoice::factory()->create();
        
        $item1 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        $item2 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);
        $item3 = InvoiceItem::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertCount(3, $invoice->items);
        $this->assertTrue($invoice->items->contains($item1));
        $this->assertTrue($invoice->items->contains($item2));
        $this->assertTrue($invoice->items->contains($item3));
    }

    /** @test */
    public function invoice_can_calculate_total_from_items()
    {
        $invoice = Invoice::factory()->create(['amount' => 0, 'total_amount' => 0]);
        
        // Créer des éléments avec des montants spécifiques
        InvoiceItem::factory()->amount(50.00)->create(['invoice_id' => $invoice->id]);
        InvoiceItem::factory()->amount(30.00)->create(['invoice_id' => $invoice->id]);
        InvoiceItem::factory()->amount(-10.00)->create(['invoice_id' => $invoice->id]); // Réduction
        InvoiceItem::factory()->amount(14.00)->create(['invoice_id' => $invoice->id]); // Taxe

        $expectedTotal = 50.00 + 30.00 - 10.00 + 14.00; // = 84.00
        $actualTotal = $invoice->items->sum('amount');

        $this->assertEquals($expectedTotal, $actualTotal);
    }

    /** @test */
    public function tenant_billing_status_is_updated_correctly()
    {
        $tenant = Tenant::factory()->create([
            'billing_status' => 'active',
            'current_plan' => 'premium'
        ]);

        $this->assertEquals('active', $tenant->billing_status);
        $this->assertEquals('premium', $tenant->current_plan);
    }

    /** @test */
    public function tenant_can_check_trial_status()
    {
        $trialTenant = Tenant::factory()->create([
            'trial_ends_at' => now()->addDays(7)
        ]);
        
        $expiredTrialTenant = Tenant::factory()->create([
            'trial_ends_at' => now()->subDays(1)
        ]);
        
        $noTrialTenant = Tenant::factory()->create([
            'trial_ends_at' => null
        ]);

        $this->assertTrue($trialTenant->onTrial());
        $this->assertFalse($expiredTrialTenant->onTrial());
        $this->assertFalse($noTrialTenant->onTrial());
    }

    /** @test */
    public function subscription_factory_states_work_correctly()
    {
        $trialSubscription = Subscription::factory()->trial()->create();
        $activeSubscription = Subscription::factory()->active()->create();
        $canceledSubscription = Subscription::factory()->canceled()->create();
        $suspendedSubscription = Subscription::factory()->suspended()->create();
        $pastDueSubscription = Subscription::factory()->pastDue()->create();
        $expiredSubscription = Subscription::factory()->expired()->create();

        $this->assertEquals('trialing', $trialSubscription->status);
        $this->assertEquals('active', $activeSubscription->status);
        $this->assertEquals('canceled', $canceledSubscription->status);
        $this->assertEquals('suspended', $suspendedSubscription->status);
        $this->assertEquals('past_due', $pastDueSubscription->status);
        $this->assertEquals('expired', $expiredSubscription->status);
    }

    /** @test */
    public function invoice_factory_states_work_correctly()
    {
        $paidInvoice = Invoice::factory()->paid()->create();
        $openInvoice = Invoice::factory()->open()->create();
        $voidInvoice = Invoice::factory()->void()->create();
        $overdueInvoice = Invoice::factory()->overdue()->create();

        $this->assertEquals('paid', $paidInvoice->status);
        $this->assertEquals('open', $openInvoice->status);
        $this->assertEquals('void', $voidInvoice->status);
        $this->assertEquals('open', $overdueInvoice->status);
        $this->assertNotNull($paidInvoice->paid_at);
        $this->assertNull($openInvoice->paid_at);
        $this->assertNotNull($voidInvoice->voided_at);
        $this->assertLessThan(now(), $overdueInvoice->due_at);
    }

    /** @test */
    public function provider_specific_factories_work_correctly()
    {
        $stripeSubscription = Subscription::factory()->stripe()->create();
        $paypalSubscription = Subscription::factory()->paypal()->create();
        
        $stripeInvoice = Invoice::factory()->stripe()->create();
        $paypalInvoice = Invoice::factory()->paypal()->create();

        $this->assertEquals('stripe', $stripeSubscription->provider);
        $this->assertEquals('paypal', $paypalSubscription->provider);
        $this->assertEquals('stripe', $stripeInvoice->provider);
        $this->assertEquals('paypal', $paypalInvoice->provider);
        
        // Vérifier les formats d'ID spécifiques aux fournisseurs
        $this->assertStringStartsWith('sub_', $stripeSubscription->provider_subscription_id);
        $this->assertStringStartsWith('I-', $paypalSubscription->provider_subscription_id);
        $this->assertStringStartsWith('in_', $stripeInvoice->provider_invoice_id);
        $this->assertStringStartsWith('INV2-', $paypalInvoice->provider_invoice_id);
    }

    /** @test */
    public function metadata_is_stored_correctly()
    {
        $subscription = Subscription::factory()->create([
            'metadata' => [
                'custom_field' => 'custom_value',
                'integration_id' => 12345
            ]
        ]);

        $invoice = Invoice::factory()->create([
            'metadata' => [
                'billing_cycle' => 'monthly',
                'proration' => false
            ]
        ]);

        $this->assertEquals('custom_value', $subscription->metadata['custom_field']);
        $this->assertEquals(12345, $subscription->metadata['integration_id']);
        $this->assertEquals('monthly', $invoice->metadata['billing_cycle']);
        $this->assertFalse($invoice->metadata['proration']);
    }
}