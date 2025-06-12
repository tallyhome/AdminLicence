<?php

namespace Tests\Unit;

use App\Http\Middleware\TenantMiddleware;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = new TenantMiddleware();
    }

    /**
     * Tester la résolution du tenant à partir de la session
     */
    public function test_resolve_tenant_from_session()
    {
        // Créer un tenant
        $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);
        
        // Créer un utilisateur et l'associer au tenant
        $user = User::factory()->create();
        $tenant->users()->attach($user->id, ['role' => 'admin']);
        
        // Simuler une requête authentifiée
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Simuler une session avec un tenant_id
        Session::shouldReceive('has')
            ->with('tenant_id')
            ->andReturn(true);
            
        Session::shouldReceive('get')
            ->with('tenant_id')
            ->andReturn($tenant->id);
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware
        $response = $this->middleware->handle($request, $next);
        
        // Vérifier que le tenant a été correctement résolu et partagé
        $this->assertEquals($tenant->id, $request->tenant->id);
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Tester la résolution du tenant à partir du paramètre de requête
     */
    public function test_resolve_tenant_from_request_parameter()
    {
        // Créer un tenant
        $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);
        
        // Créer un utilisateur et l'associer au tenant
        $user = User::factory()->create();
        $tenant->users()->attach($user->id, ['role' => 'admin']);
        
        // Simuler une requête authentifiée avec le paramètre tenant_id
        $request = Request::create('/dashboard', 'GET', ['tenant_id' => $tenant->id]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Simuler une session sans tenant_id
        Session::shouldReceive('has')
            ->with('tenant_id')
            ->andReturn(false);
            
        Session::shouldReceive('put')
            ->with('tenant_id', $tenant->id)
            ->once();
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware
        $response = $this->middleware->handle($request, $next);
        
        // Vérifier que le tenant a été correctement résolu et partagé
        $this->assertEquals($tenant->id, $request->tenant->id);
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Tester le cas où l'utilisateur n'a pas accès au tenant
     */
    public function test_user_without_tenant_access()
    {
        // Créer un tenant
        $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_ACTIVE]);
        
        // Créer un utilisateur sans l'associer au tenant
        $user = User::factory()->create();
        
        // Simuler une requête authentifiée
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Simuler une session avec un tenant_id
        Session::shouldReceive('has')
            ->with('tenant_id')
            ->andReturn(true);
            
        Session::shouldReceive('get')
            ->with('tenant_id')
            ->andReturn($tenant->id);
            
        Session::shouldReceive('forget')
            ->with('tenant_id')
            ->once();
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware
        $response = $this->middleware->handle($request, $next);
        
        // Vérifier que l'utilisateur est redirigé vers la sélection de tenant
        $this->assertTrue($response->isRedirect(route('tenant.select')));
    }

    /**
     * Tester le cas où le tenant est suspendu
     */
    public function test_suspended_tenant()
    {
        // Créer un tenant suspendu
        $tenant = Tenant::factory()->create(['status' => Tenant::STATUS_SUSPENDED]);
        
        // Créer un utilisateur et l'associer au tenant
        $user = User::factory()->create();
        $tenant->users()->attach($user->id, ['role' => 'admin']);
        
        // Simuler une requête authentifiée
        $request = Request::create('/dashboard', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        // Simuler une session avec un tenant_id
        Session::shouldReceive('has')
            ->with('tenant_id')
            ->andReturn(true);
            
        Session::shouldReceive('get')
            ->with('tenant_id')
            ->andReturn($tenant->id);
            
        Session::shouldReceive('forget')
            ->with('tenant_id')
            ->once();
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware
        $response = $this->middleware->handle($request, $next);
        
        // Vérifier que l'utilisateur est redirigé vers la sélection de tenant
        $this->assertTrue($response->isRedirect(route('tenant.select')));
    }
}
