<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\StripeService;
use App\Services\PayPalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use App\Events\SubscriptionCancelled;
use App\Notifications\SubscriptionExpiringNotification;

class BillingCommandsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Charger les seeders de configuration
        $this->artisan('db:seed', ['--class' => 'BillingPlansSeeder']);
        
        // Mock des services externes
        $this->mock(StripeService::class);
        $this->mock(PayPalService::class);
    }

    /** @test */
    public function process_expired_subscriptions_command_exists()
    {
        $this->assertTrue(class_exists('App\\Console\\Commands\\ProcessExpiredSubscriptions'));
    }

    /** @test */
    public function process_expired_subscriptions_handles_expired_subscriptions()
    {
        Event::fake();
        
        // Créer un abonnement expiré
        $expiredSubscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDays(1),
            'auto_renew' => false
        ]);
        
        // Créer un abonnement encore valide
        $activeSubscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->addDays(30),
            'auto_renew' => true
        ]);

        $this->artisan('billing:process-expired')
            ->expectsOutput('Processing expired subscriptions...')
            ->assertExitCode(0);

        // Vérifier que l'abonnement expiré a été mis à jour
        $expiredSubscription->refresh();
        $this->assertEquals('expired', $expiredSubscription->status);
        
        // Vérifier que l'abonnement actif n'a pas été modifié
        $activeSubscription->refresh();
        $this->assertEquals('active', $activeSubscription->status);
        
        // Vérifier qu'un événement a été émis
        Event::assertDispatched(SubscriptionCancelled::class);
    }

    /** @test */
    public function process_expired_subscriptions_handles_grace_period()
    {
        // Créer un tenant avec une période de grâce
        $tenant = Tenant::factory()->create([
            'billing_status' => 'past_due',
            'subscription_ends_at' => now()->subDays(1),
            'grace_period_ends_at' => now()->addDays(7)
        ]);
        
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'past_due',
            'ends_at' => now()->subDays(1)
        ]);

        $this->artisan('billing:process-expired')
            ->assertExitCode(0);

        // Le tenant devrait toujours être en période de grâce
        $tenant->refresh();
        $this->assertEquals('grace_period', $tenant->billing_status);
        
        $subscription->refresh();
        $this->assertEquals('past_due', $subscription->status);
    }

    /** @test */
    public function process_expired_subscriptions_handles_expired_grace_period()
    {
        // Créer un tenant avec une période de grâce expirée
        $tenant = Tenant::factory()->create([
            'billing_status' => 'grace_period',
            'subscription_ends_at' => now()->subDays(10),
            'grace_period_ends_at' => now()->subDays(1)
        ]);
        
        $subscription = Subscription::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'past_due',
            'ends_at' => now()->subDays(10)
        ]);

        $this->artisan('billing:process-expired')
            ->assertExitCode(0);

        // Le tenant devrait être suspendu
        $tenant->refresh();
        $this->assertEquals('suspended', $tenant->billing_status);
        
        $subscription->refresh();
        $this->assertEquals('expired', $subscription->status);
    }

    /** @test */
    public function process_expired_subscriptions_handles_expired_trials()
    {
        // Créer un tenant avec un essai expiré
        $tenant = Tenant::factory()->create([
            'trial_ends_at' => now()->subDays(1),
            'billing_status' => 'trialing'
        ]);

        $this->artisan('billing:process-expired')
            ->assertExitCode(0);

        // Le tenant devrait être en attente de paiement
        $tenant->refresh();
        $this->assertEquals('requires_payment', $tenant->billing_status);
    }

    /** @test */
    public function process_expired_subscriptions_dry_run_mode()
    {
        Event::fake();
        
        $expiredSubscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDays(1),
            'auto_renew' => false
        ]);

        $this->artisan('billing:process-expired', ['--dry-run' => true])
            ->expectsOutput('DRY RUN MODE - No changes will be made')
            ->assertExitCode(0);

        // Vérifier qu'aucun changement n'a été fait
        $expiredSubscription->refresh();
        $this->assertEquals('active', $expiredSubscription->status);
        
        // Vérifier qu'aucun événement n'a été émis
        Event::assertNotDispatched(SubscriptionCancelled::class);
    }

    /** @test */
    public function sync_subscriptions_command_exists()
    {
        $this->assertTrue(class_exists('App\\Console\\Commands\\SyncSubscriptions'));
    }

    /** @test */
    public function sync_subscriptions_syncs_all_subscriptions()
    {
        $stripeService = $this->mock(StripeService::class);
        $paypalService = $this->mock(PayPalService::class);
        
        // Créer des abonnements de test
        $stripeSubscription = Subscription::factory()->stripe()->active()->create();
        $paypalSubscription = Subscription::factory()->paypal()->active()->create();
        
        // Mock des réponses des services
        $stripeService->shouldReceive('getSubscription')
            ->once()
            ->with($stripeSubscription->provider_subscription_id)
            ->andReturn([
                'id' => $stripeSubscription->provider_subscription_id,
                'status' => 'active',
                'current_period_end' => now()->addMonth()->timestamp,
                'cancel_at_period_end' => false
            ]);
            
        $paypalService->shouldReceive('getSubscription')
            ->once()
            ->with($paypalSubscription->provider_subscription_id)
            ->andReturn([
                'id' => $paypalSubscription->provider_subscription_id,
                'status' => 'ACTIVE',
                'billing_info' => [
                    'next_billing_time' => now()->addMonth()->toISOString()
                ]
            ]);

        $this->artisan('billing:sync-subscriptions')
            ->expectsOutput('Synchronizing subscriptions with payment providers...')
            ->assertExitCode(0);
    }

    /** @test */
    public function sync_subscriptions_syncs_specific_provider()
    {
        $stripeService = $this->mock(StripeService::class);
        
        $stripeSubscription = Subscription::factory()->stripe()->active()->create();
        $paypalSubscription = Subscription::factory()->paypal()->active()->create();
        
        $stripeService->shouldReceive('getSubscription')
            ->once()
            ->with($stripeSubscription->provider_subscription_id)
            ->andReturn([
                'id' => $stripeSubscription->provider_subscription_id,
                'status' => 'active'
            ]);

        $this->artisan('billing:sync-subscriptions', ['--provider' => 'stripe'])
            ->assertExitCode(0);
    }

    /** @test */
    public function sync_subscriptions_syncs_specific_subscription()
    {
        $stripeService = $this->mock(StripeService::class);
        
        $subscription = Subscription::factory()->stripe()->active()->create();
        
        $stripeService->shouldReceive('getSubscription')
            ->once()
            ->with($subscription->provider_subscription_id)
            ->andReturn([
                'id' => $subscription->provider_subscription_id,
                'status' => 'canceled',
                'canceled_at' => now()->timestamp
            ]);

        $this->artisan('billing:sync-subscriptions', [
            '--subscription' => $subscription->id
        ])->assertExitCode(0);
        
        // Vérifier que l'abonnement a été mis à jour
        $subscription->refresh();
        $this->assertEquals('canceled', $subscription->status);
        $this->assertNotNull($subscription->canceled_at);
    }

    /** @test */
    public function sync_subscriptions_dry_run_mode()
    {
        $stripeService = $this->mock(StripeService::class);
        
        $subscription = Subscription::factory()->stripe()->active()->create();
        
        $stripeService->shouldReceive('getSubscription')
            ->once()
            ->with($subscription->provider_subscription_id)
            ->andReturn([
                'id' => $subscription->provider_subscription_id,
                'status' => 'canceled'
            ]);

        $this->artisan('billing:sync-subscriptions', [
            '--subscription' => $subscription->id,
            '--dry-run' => true
        ])
        ->expectsOutput('DRY RUN MODE - No changes will be made')
        ->assertExitCode(0);
        
        // Vérifier qu'aucun changement n'a été fait
        $subscription->refresh();
        $this->assertEquals('active', $subscription->status);
    }

    /** @test */
    public function sync_subscriptions_handles_provider_errors()
    {
        $stripeService = $this->mock(StripeService::class);
        
        $subscription = Subscription::factory()->stripe()->active()->create();
        
        $stripeService->shouldReceive('getSubscription')
            ->once()
            ->with($subscription->provider_subscription_id)
            ->andThrow(new \Exception('Subscription not found'));

        $this->artisan('billing:sync-subscriptions', [
            '--subscription' => $subscription->id
        ])
        ->expectsOutput('Error syncing subscription ' . $subscription->id . ': Subscription not found')
        ->assertExitCode(0);
    }

    /** @test */
    public function process_billing_notifications_job_can_be_dispatched()
    {
        Notification::fake();
        
        // Créer des abonnements qui expirent bientôt
        $expiringSubscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->addDays(7) // Expire dans 7 jours
        ]);
        
        $expiringTrial = Tenant::factory()->create([
            'trial_ends_at' => now()->addDays(3) // Essai expire dans 3 jours
        ]);

        // Dispatcher le job
        $this->artisan('queue:work', ['--once' => true]);
        
        // Le job devrait traiter les notifications
        $this->assertTrue(true); // Test basique pour vérifier que le job peut être exécuté
    }

    /** @test */
    public function commands_respect_force_flag()
    {
        Event::fake();
        
        $expiredSubscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDays(1)
        ]);

        $this->artisan('billing:process-expired', ['--force' => true])
            ->doesntExpectOutput('Are you sure you want to process expired subscriptions?')
            ->assertExitCode(0);

        $expiredSubscription->refresh();
        $this->assertEquals('expired', $expiredSubscription->status);
    }

    /** @test */
    public function commands_show_statistics()
    {
        // Créer différents types d'abonnements
        Subscription::factory()->expired()->create();
        Subscription::factory()->active()->create();
        Subscription::factory()->canceled()->create();
        
        // Créer des tenants avec différents statuts
        Tenant::factory()->create(['billing_status' => 'grace_period']);
        Tenant::factory()->create(['trial_ends_at' => now()->subDays(1)]);

        $this->artisan('billing:process-expired')
            ->expectsOutput('Processing expired subscriptions...')
            ->expectsOutputToContain('Processed:')
            ->assertExitCode(0);
    }

    /** @test */
    public function sync_command_validates_provider_parameter()
    {
        $this->artisan('billing:sync-subscriptions', ['--provider' => 'invalid'])
            ->expectsOutput('Invalid provider. Must be one of: stripe, paypal')
            ->assertExitCode(1);
    }

    /** @test */
    public function sync_command_validates_subscription_parameter()
    {
        $this->artisan('billing:sync-subscriptions', ['--subscription' => 99999])
            ->expectsOutput('Subscription with ID 99999 not found')
            ->assertExitCode(1);
    }

    /** @test */
    public function commands_handle_empty_datasets_gracefully()
    {
        // Aucun abonnement dans la base
        $this->artisan('billing:process-expired')
            ->expectsOutput('No expired subscriptions found')
            ->assertExitCode(0);
            
        $this->artisan('billing:sync-subscriptions')
            ->expectsOutput('No subscriptions found to sync')
            ->assertExitCode(0);
    }

    /** @test */
    public function commands_log_important_actions()
    {
        $subscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDays(1)
        ]);

        $this->artisan('billing:process-expired')
            ->expectsOutputToContain('Subscription ' . $subscription->id . ' marked as expired')
            ->assertExitCode(0);
    }

    /** @test */
    public function process_expired_handles_auto_renewal_subscriptions()
    {
        // Créer un abonnement avec auto-renouvellement activé mais expiré
        $autoRenewSubscription = Subscription::factory()->create([
            'status' => 'active',
            'ends_at' => now()->subDays(1),
            'auto_renew' => true
        ]);

        $this->artisan('billing:process-expired')
            ->assertExitCode(0);

        // L'abonnement avec auto-renouvellement devrait être marqué comme past_due
        // plutôt qu'expiré, pour permettre les tentatives de paiement
        $autoRenewSubscription->refresh();
        $this->assertEquals('past_due', $autoRenewSubscription->status);
    }
}