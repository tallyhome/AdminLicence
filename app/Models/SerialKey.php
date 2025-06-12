<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SerialKey extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serial_key',
        'status',
        'project_id',
        'domain',
        'ip_address',
        'expires_at',
        'licence_type',
        'max_accounts',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'max_accounts' => 'integer',
        'features' => 'array',
        'limits' => 'array',
        'metadata' => 'array',
        'is_saas_enabled' => 'boolean',
    ];
    
    /**
     * Les types de licence disponibles
     */
    const LICENCE_TYPE_SINGLE = 'single';
    const LICENCE_TYPE_MULTI = 'multi';

    /**
     * Get the project that owns the serial key.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relation avec les tenants
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    /**
     * Obtient le tenant principal (pour les licences mono-compte)
     */
    public function primaryTenant()
    {
        return $this->tenants()->where('is_primary', true)->first();
    }

    /**
     * Compte le nombre de tenants actifs
     */
    public function getActiveTenantCount(): int
    {
        return $this->tenants()->where('status', 'active')->count();
    }

    /**
     * Vérifie si la licence peut créer un nouveau tenant
     */
    public function canCreateTenant(): bool
    {
        if (!$this->isSaasEnabled()) {
            return false;
        }

        $maxTenants = $this->max_tenants;
        if ($maxTenants === null) {
            return true; // Illimité
        }

        return $this->getActiveTenantCount() < $maxTenants;
    }

    /**
     * Generate a unique serial key.
     */
    public static function generateUniqueKey(): string
    {
        $key = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        
        // Ensure the key is unique
        while (self::where('serial_key', $key)->exists()) {
            $key = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        }
        
        return $key;
    }

    /**
     * Check if the serial key is valid.
     */
    public function isValid(): bool
    {
        return $this->status === 'active' && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if the domain is authorized for this key.
     */
    public function isDomainAuthorized(string $domain): bool
    {
        return $this->domain === null || $this->domain === $domain;
    }

    /**
     * Check if the IP address is authorized for this key.
     */
    public function isIpAuthorized(string $ipAddress): bool
    {
        return $this->ip_address === null || $this->ip_address === $ipAddress;
    }

    /**
     * Vérifie si la licence est de type multi-compte
     */
    public function isMultiAccount(): bool
    {
        return $this->licence_type === self::LICENCE_TYPE_MULTI;
    }

    /**
     * Vérifie si la licence active le mode SaaS
     */
    public function isSaasEnabled(): bool
    {
        return $this->is_saas_enabled === true;
    }

    /**
     * Obtient le mode de fonctionnement de la licence
     */
    public function getMode(): string
    {
        return $this->isSaasEnabled() ? 'saas' : 'single';
    }

    /**
     * Vérifie si une fonctionnalité est activée
     */
    public function hasFeature(string $feature): bool
    {
        return isset($this->features[$feature]) && $this->features[$feature] === true;
    }

    /**
     * Obtient la limite pour un élément spécifique
     */
    public function getLimit(string $item): ?int
    {
        return $this->limits[$item] ?? null;
    }

    /**
     * Vérifie si une limite est atteinte
     */
    public function isLimitReached(string $item, int $currentCount): bool
    {
        $limit = $this->getLimit($item);
        return $limit !== null && $currentCount >= $limit;
    }

    /**
     * Obtient les fonctionnalités par défaut selon le type de licence
     */
    public static function getDefaultFeatures(string $licenceType, bool $isSaas = false): array
    {
        $baseFeatures = [
            'project_management' => true,
            'api_access' => true,
            'email_templates' => true,
            'basic_support' => true,
        ];

        if ($isSaas || $licenceType === 'multi') {
            $baseFeatures = array_merge($baseFeatures, [
                'multi_tenant' => true,
                'advanced_analytics' => true,
                'priority_support' => true,
                'white_label' => true,
                'custom_domains' => true,
            ]);
        }

        return $baseFeatures;
    }

    /**
     * Obtient les limites par défaut selon le type de licence
     */
    public static function getDefaultLimits(string $licenceType, bool $isSaas = false): array
    {
        if ($isSaas || $licenceType === 'multi') {
            return [
                'projects' => null, // Illimité
                'api_calls_per_month' => 1000000,
                'email_templates' => null, // Illimité
                'storage_gb' => 100,
            ];
        }

        return [
            'projects' => 10,
            'api_calls_per_month' => 10000,
            'email_templates' => 5,
            'storage_gb' => 1,
        ];
    }
    
    /**
     * Vérifie si la licence est de type mono-compte
     */
    public function isSingleAccount(): bool
    {
        return $this->licence_type === self::LICENCE_TYPE_SINGLE;
    }
    
    /**
     * Retourne le nombre maximum de comptes autorisés pour cette licence
     */
    public function getMaxAccounts(): int
    {
        if ($this->isMultiAccount()) {
            return $this->max_accounts ?? 1;
        }
        
        return 1; // Les licences mono-compte ne peuvent avoir qu'un seul compte
    }
    
    /**
     * Vérifie si la licence peut accepter de nouveaux tenants
     */
    public function canAcceptMoreTenants(): bool
    {
        if (!$this->isMultiAccount()) {
            return false; // Les licences mono-compte ne peuvent pas avoir de tenants supplémentaires
        }
        
        return $this->getActiveTenantCount() < $this->getMaxAccounts();
    }
    
    /**
     * Relation avec l'historique
     */
    public function history()
    {
        return $this->hasMany(SerialKeyHistory::class);
    }
}