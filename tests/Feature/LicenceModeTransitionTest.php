<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Licence;
use App\Models\Tenant;
use App\Services\LicenceModeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class LicenceModeTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected $licenceModeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->licenceModeService = app(LicenceModeService::class);
    }

    /** @test */
    public function it_detects_single_account_mode_when_only_single_account_licence_exists()
    {
        // Créer une licence mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $mode = $this->licenceModeService->detectMode();
        
        $this->assertEquals('single_account', $mode);
        $this->assertTrue($this->licenceModeService->isSingleAccountMode());
        $this->assertFalse($this->licenceModeService->isSaasMode());
    }

    /** @test */
    public function it_detects_saas_mode_when_saas_licence_exists()
    {
        // Créer une licence SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        $mode = $this->licenceModeService->detectMode();
        
        $this->assertEquals('saas', $mode);
        $this->assertTrue($this->licenceModeService->isSaasMode());
        $this->assertFalse($this->licenceModeService->isSingleAccountMode());
    }

    /** @test */
    public function it_prioritizes_saas_mode_when_both_licences_exist()
    {
        // Créer les deux types de licences
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);
        
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        $mode = $this->licenceModeService->detectMode();
        
        $this->assertEquals('saas', $mode);
    }

    /** @test */
    public function it_transitions_from_single_account_to_saas_mode()
    {
        // Commencer en mode mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $this->assertEquals('single_account', $this->licenceModeService->getCurrentMode());

        // Ajouter une licence SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        // Rafraîchir le mode
        $this->licenceModeService->refreshMode();

        $this->assertEquals('saas', $this->licenceModeService->getCurrentMode());
    }

    /** @test */
    public function it_transitions_from_saas_to_single_account_mode()
    {
        // Commencer en mode SaaS
        $saasLicence = Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);
        
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $this->assertEquals('saas', $this->licenceModeService->getCurrentMode());

        // Désactiver la licence SaaS
        $saasLicence->update(['status' => 'inactive']);

        // Rafraîchir le mode
        $this->licenceModeService->refreshMode();

        $this->assertEquals('single_account', $this->licenceModeService->getCurrentMode());
    }

    /** @test */
    public function it_clears_cache_on_mode_transition()
    {
        // Mettre en cache un mode
        Cache::put('licence_mode_current', 'single_account', 3600);
        
        // Créer une licence SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        // Rafraîchir le mode
        $this->licenceModeService->refreshMode();

        // Vérifier que le cache a été mis à jour
        $this->assertEquals('saas', Cache::get('licence_mode_current'));
    }

    /** @test */
    public function it_maintains_data_consistency_during_transition()
    {
        // Créer des données en mode mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $this->assertEquals('single_account', $this->licenceModeService->getCurrentMode());

        // Passer en mode SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        $this->licenceModeService->refreshMode();

        // Vérifier que les données sont cohérentes
        $this->assertEquals('saas', $this->licenceModeService->getCurrentMode());
        $this->assertNotEmpty($this->licenceModeService->getAvailableFeatures());
        $this->assertNotEmpty($this->licenceModeService->getLimits());
    }

    /** @test */
    public function it_handles_licence_activation_and_deactivation()
    {
        // Créer une licence inactive
        $licence = Licence::factory()->create([
            'type' => 'saas',
            'status' => 'inactive'
        ]);

        // Aucune licence active, mode par défaut
        $this->assertEquals('single_account', $this->licenceModeService->getCurrentMode());

        // Activer la licence
        $licence->update(['status' => 'active']);
        $this->licenceModeService->refreshMode();

        $this->assertEquals('saas', $this->licenceModeService->getCurrentMode());

        // Désactiver la licence
        $licence->update(['status' => 'inactive']);
        $this->licenceModeService->refreshMode();

        $this->assertEquals('single_account', $this->licenceModeService->getCurrentMode());
    }

    /** @test */
    public function it_validates_interface_consistency_after_transition()
    {
        // Commencer en mode mono-compte
        Licence::factory()->create([
            'type' => 'single_account',
            'status' => 'active'
        ]);

        $features = $this->licenceModeService->getAvailableFeatures();
        $this->assertNotContains('multi_tenant', $features);

        // Passer en mode SaaS
        Licence::factory()->create([
            'type' => 'saas',
            'status' => 'active'
        ]);

        $this->licenceModeService->refreshMode();

        $features = $this->licenceModeService->getAvailableFeatures();
        $this->assertContains('multi_tenant', $features);
    }
}