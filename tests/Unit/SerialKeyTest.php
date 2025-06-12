<?php

namespace Tests\Unit;

use App\Models\SerialKey;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerialKeyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tester la création d'une licence mono-compte
     */
    public function test_create_single_account_licence()
    {
        $licence = SerialKey::factory()->create([
            'licence_type' => SerialKey::LICENCE_TYPE_SINGLE,
            'max_accounts' => null,
        ]);

        $this->assertEquals(SerialKey::LICENCE_TYPE_SINGLE, $licence->licence_type);
        $this->assertNull($licence->max_accounts);
        $this->assertTrue($licence->isSingleAccount());
        $this->assertFalse($licence->isMultiAccount());
        $this->assertEquals(1, $licence->getMaxAccounts());
    }

    /**
     * Tester la création d'une licence multi-comptes
     */
    public function test_create_multi_account_licence()
    {
        $maxAccounts = 5;
        $licence = SerialKey::factory()->create([
            'licence_type' => SerialKey::LICENCE_TYPE_MULTI,
            'max_accounts' => $maxAccounts,
        ]);

        $this->assertEquals(SerialKey::LICENCE_TYPE_MULTI, $licence->licence_type);
        $this->assertEquals($maxAccounts, $licence->max_accounts);
        $this->assertTrue($licence->isMultiAccount());
        $this->assertFalse($licence->isSingleAccount());
        $this->assertEquals($maxAccounts, $licence->getMaxAccounts());
    }

    /**
     * Tester la relation avec les tenants
     */
    public function test_licence_tenants_relationship()
    {
        $licence = SerialKey::factory()->create([
            'licence_type' => SerialKey::LICENCE_TYPE_MULTI,
            'max_accounts' => 3,
        ]);

        // Créer des tenants associés à cette licence
        $tenant1 = Tenant::factory()->create(['licence_id' => $licence->id]);
        $tenant2 = Tenant::factory()->create(['licence_id' => $licence->id]);

        $this->assertCount(2, $licence->tenants);
        $this->assertEquals($tenant1->id, $licence->tenants[0]->id);
        $this->assertEquals($tenant2->id, $licence->tenants[1]->id);
    }

    /**
     * Tester le comptage des tenants actifs
     */
    public function test_active_tenants_count()
    {
        $licence = SerialKey::factory()->create([
            'licence_type' => SerialKey::LICENCE_TYPE_MULTI,
            'max_accounts' => 5,
        ]);

        // Créer des tenants avec différents statuts
        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_SUSPENDED,
        ]);

        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_INACTIVE,
        ]);

        $this->assertEquals(2, $licence->activeTenantsCount());
        $this->assertTrue($licence->canAcceptMoreTenants());
    }

    /**
     * Tester la limite de tenants
     */
    public function test_tenant_limit_reached()
    {
        $licence = SerialKey::factory()->create([
            'licence_type' => SerialKey::LICENCE_TYPE_MULTI,
            'max_accounts' => 2,
        ]);

        // Créer des tenants jusqu'à atteindre la limite
        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        $this->assertEquals(2, $licence->activeTenantsCount());
        $this->assertFalse($licence->canAcceptMoreTenants());
    }

    /**
     * Tester la limite de tenants avec des tenants inactifs
     */
    public function test_tenant_limit_with_inactive_tenants()
    {
        $licence = SerialKey::factory()->create([
            'licence_type' => SerialKey::LICENCE_TYPE_MULTI,
            'max_accounts' => 2,
        ]);

        // Créer un tenant actif et un tenant inactif
        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        Tenant::factory()->create([
            'licence_id' => $licence->id,
            'status' => Tenant::STATUS_INACTIVE,
        ]);

        $this->assertEquals(1, $licence->activeTenantsCount());
        $this->assertTrue($licence->canAcceptMoreTenants());
    }
}
