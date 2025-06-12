<?php

namespace Tests\Unit;

use App\Http\Middleware\LicenseVerificationRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LicenseVerificationRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    protected $middleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = new LicenseVerificationRateLimiter();
    }

    /**
     * Tester que le middleware laisse passer les requêtes sous la limite
     */
    public function test_allows_requests_under_limit()
    {
        // Simuler une requête avec une clé de licence
        $request = Request::create('/api/check-serial', 'POST', [
            'serial_key' => 'TEST-KEY-123456',
            'domain' => 'example.com'
        ]);
        
        // Simuler l'adresse IP du client
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        
        // S'assurer que le rate limiter n'a pas encore été atteint
        RateLimiter::clear('license_verification:192.168.1.1:TEST-KEY-123456');
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware avec une limite de 5 tentatives par minute
        $response = $this->middleware->handle($request, $next, 5, 1);
        
        // Vérifier que la requête est autorisée
        $this->assertEquals('OK', $response->getContent());
        
        // Vérifier que les en-têtes de rate limiting sont présents
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
    }

    /**
     * Tester que le middleware bloque les requêtes au-delà de la limite
     */
    public function test_blocks_requests_over_limit()
    {
        // Simuler une requête avec une clé de licence
        $request = Request::create('/api/check-serial', 'POST', [
            'serial_key' => 'TEST-KEY-123456',
            'domain' => 'example.com'
        ]);
        
        // Simuler l'adresse IP du client
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        
        // Définir la clé de rate limiting
        $key = 'license_verification:192.168.1.1:TEST-KEY-123456';
        
        // Effacer les tentatives précédentes
        RateLimiter::clear($key);
        
        // Simuler 5 tentatives (limite maximale)
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware avec une limite de 5 tentatives par minute
        $response = $this->middleware->handle($request, $next, 5, 1);
        
        // Vérifier que la requête est bloquée avec un code 429
        $this->assertEquals(429, $response->getStatusCode());
        $this->assertEquals('Too Many Attempts', $response->getContent());
        
        // Vérifier que les en-têtes de rate limiting sont présents
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
        $this->assertTrue($response->headers->has('Retry-After'));
        $this->assertTrue($response->headers->has('X-RateLimit-Reset'));
    }

    /**
     * Tester que le middleware utilise différentes clés pour différentes adresses IP
     */
    public function test_different_keys_for_different_ips()
    {
        // Simuler une requête avec une clé de licence depuis une première IP
        $request1 = Request::create('/api/check-serial', 'POST', [
            'serial_key' => 'TEST-KEY-123456',
            'domain' => 'example.com'
        ]);
        $request1->server->set('REMOTE_ADDR', '192.168.1.1');
        
        // Simuler une requête avec la même clé de licence depuis une autre IP
        $request2 = Request::create('/api/check-serial', 'POST', [
            'serial_key' => 'TEST-KEY-123456',
            'domain' => 'example.com'
        ]);
        $request2->server->set('REMOTE_ADDR', '192.168.1.2');
        
        // Définir la clé de rate limiting pour la première IP
        $key1 = 'license_verification:192.168.1.1:TEST-KEY-123456';
        
        // Effacer les tentatives précédentes
        RateLimiter::clear($key1);
        
        // Simuler 5 tentatives (limite maximale) pour la première IP
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key1, 60);
        }
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware pour la première IP (devrait être bloqué)
        $response1 = $this->middleware->handle($request1, $next, 5, 1);
        
        // Exécuter le middleware pour la seconde IP (devrait être autorisé)
        $response2 = $this->middleware->handle($request2, $next, 5, 1);
        
        // Vérifier que la première requête est bloquée
        $this->assertEquals(429, $response1->getStatusCode());
        
        // Vérifier que la seconde requête est autorisée
        $this->assertEquals('OK', $response2->getContent());
    }

    /**
     * Tester que le middleware utilise différentes clés pour différentes licences
     */
    public function test_different_keys_for_different_licenses()
    {
        // Simuler une requête avec une première clé de licence
        $request1 = Request::create('/api/check-serial', 'POST', [
            'serial_key' => 'TEST-KEY-123456',
            'domain' => 'example.com'
        ]);
        $request1->server->set('REMOTE_ADDR', '192.168.1.1');
        
        // Simuler une requête avec une autre clé de licence depuis la même IP
        $request2 = Request::create('/api/check-serial', 'POST', [
            'serial_key' => 'TEST-KEY-789012',
            'domain' => 'example.com'
        ]);
        $request2->server->set('REMOTE_ADDR', '192.168.1.1');
        
        // Définir la clé de rate limiting pour la première licence
        $key1 = 'license_verification:192.168.1.1:TEST-KEY-123456';
        
        // Effacer les tentatives précédentes
        RateLimiter::clear($key1);
        
        // Simuler 5 tentatives (limite maximale) pour la première licence
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key1, 60);
        }
        
        // Créer une closure pour le middleware
        $next = function ($request) {
            return response('OK');
        };
        
        // Exécuter le middleware pour la première licence (devrait être bloqué)
        $response1 = $this->middleware->handle($request1, $next, 5, 1);
        
        // Exécuter le middleware pour la seconde licence (devrait être autorisé)
        $response2 = $this->middleware->handle($request2, $next, 5, 1);
        
        // Vérifier que la première requête est bloquée
        $this->assertEquals(429, $response1->getStatusCode());
        
        // Vérifier que la seconde requête est autorisée
        $this->assertEquals('OK', $response2->getContent());
    }
}
