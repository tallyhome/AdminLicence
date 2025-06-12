<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Licence;
use App\Http\Middleware\CheckLicenceMode;
use App\Http\Middleware\CheckLimits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LicenceModeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function check_licence_mode_middleware_allows_access_with_correct_mode()
    {
        // Créer une licence SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.tenants.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function check_licence_mode_middleware_blocks_saas_routes_in_single_account_mode()
    {
        // Créer une licence mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.tenants.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function check_licence_mode_middleware_redirects_with_error_message()
    {
        // Créer une licence mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.tenants.index'));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function check_licence_mode_middleware_returns_json_for_api_requests()
    {
        // Créer une licence mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->getJson(route('admin.tenants.index'));

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Accès non autorisé',
                'current_mode' => 'single_account',
                'required_mode' => 'saas'
            ]);
    }

    /** @test */
    public function check_limits_middleware_allows_access_when_limit_not_reached()
    {
        // Créer une licence avec limites
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active',
            'limits' => [
                'projects' => 5,
                'serial_keys' => 100
            ]
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.projects.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function check_limits_middleware_blocks_access_when_limit_reached()
    {
        // Créer une licence avec limites strictes
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active',
            'limits' => [
                'projects' => 0, // Limite atteinte
                'serial_keys' => 100
            ]
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.projects.create'));

        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function check_limits_middleware_returns_appropriate_error_message()
    {
        // Créer une licence avec limites strictes
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active',
            'limits' => [
                'projects' => 0
            ]
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.projects.create'));

        $response->assertRedirect()
            ->assertSessionHas('error')
            ->assertSessionHas('limit_reached', true)
            ->assertSessionHas('limit_type', 'projects');
    }

    /** @test */
    public function check_limits_middleware_suggests_upgrade_for_single_account_mode()
    {
        // Créer une licence mono-compte avec limites
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active',
            'limits' => [
                'projects' => 0
            ]
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->getJson(route('admin.projects.create'));

        $response->assertStatus(429)
            ->assertJson([
                'error' => 'Limite atteinte',
                'upgrade_required' => true
            ]);
    }

    /** @test */
    public function inject_licence_mode_middleware_shares_data_with_views()
    {
        // Créer une licence SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Vérifier que les données sont disponibles dans la vue
        $response->assertViewHas('licenceMode');
        $response->assertViewHas('hasFeature');
        $response->assertViewHas('isLimitReached');
        $response->assertViewHas('canAccessRoute');
    }

    /** @test */
    public function middleware_chain_works_correctly()
    {
        // Créer une licence SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active',
            'limits' => [
                'tenants' => 10
            ]
        ]);

        // Test d'accès à une route SaaS avec vérification de limites
        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.tenants.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function middleware_logs_access_attempts()
    {
        // Créer une licence mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        // Tenter d'accéder à une route SaaS
        $this->actingAs($this->user, 'admin')
            ->get(route('admin.tenants.index'));

        // Vérifier que l'accès refusé a été loggé
        $this->assertDatabaseHas('logs', [
            'level' => 'warning',
            'message' => 'Accès refusé - Mode requis: saas, Mode actuel: single_account'
        ]);
    }

    /** @test */
    public function middleware_handles_route_accessibility_correctly()
    {
        // Créer une licence mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        // Routes accessibles en mode mono-compte
        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.projects.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.serial-keys.index'));
        $response->assertStatus(200);

        // Routes non accessibles en mode mono-compte
        $response = $this->actingAs($this->user, 'admin')
            ->get(route('admin.tenants.index'));
        $response->assertStatus(403);
    }
}