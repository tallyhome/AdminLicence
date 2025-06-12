<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'domain',
        'database',
        'status',
        'settings',
        'licence_id',
        'subscription_id',
        'subscription_status',
        'subscription_ends_at',
        'trial_ends_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'subscription_ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'licence_features' => 'array',
        'usage_stats' => 'array',
        'is_primary' => 'boolean',
        'licence_expires_at' => 'datetime',
    ];

    /**
     * Tenant status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Subscription status constants
     */
    const SUBSCRIPTION_ACTIVE = 'active';
    const SUBSCRIPTION_CANCELED = 'canceled';
    const SUBSCRIPTION_EXPIRED = 'expired';
    const SUBSCRIPTION_TRIAL = 'trial';

    /**
     * Get the clients associated with the tenant.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Relation avec la licence (SerialKey)
     */
    public function serialKey()
    {
        return $this->belongsTo(SerialKey::class);
    }

    /**
     * Vérifie si c'est le tenant principal
     */
    public function isPrimary(): bool
    {
        return $this->is_primary === true;
    }

    /**
     * Vérifie si le tenant est en mode SaaS
     */
    public function isSaasMode(): bool
    {
        return $this->licence_mode === 'saas';
    }

    /**
     * Vérifie si le tenant est en mode mono-compte
     */
    public function isSingleMode(): bool
    {
        return $this->licence_mode === 'single';
    }

    /**
     * Vérifie si une fonctionnalité est activée pour ce tenant
     */
    public function hasFeature(string $feature): bool
    {
        return isset($this->licence_features[$feature]) && $this->licence_features[$feature] === true;
    }

    /**
     * Obtient la limite pour un élément spécifique
     */
    public function getLimit(string $item): ?int
    {
        // Vérifier d'abord les limites spécifiques au tenant
        if ($item === 'clients' && $this->max_clients !== null) {
            return $this->max_clients;
        }
        if ($item === 'projects' && $this->max_projects !== null) {
            return $this->max_projects;
        }

        // Sinon, utiliser les limites de la licence
        return $this->serialKey?->getLimit($item);
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
     * Compte le nombre de clients actifs
     */
    public function getActiveClientCount(): int
    {
        return $this->clients()->where('status', 'active')->count();
    }

    /**
     * Vérifie si le tenant peut créer un nouveau client
     */
    public function canCreateClient(): bool
    {
        $maxClients = $this->getLimit('clients');
        if ($maxClients === null) {
            return true; // Illimité
        }

        return $this->getActiveClientCount() < $maxClients;
    }

    /**
     * Met à jour les statistiques d'utilisation
     */
    public function updateUsageStats(): void
    {
        $stats = [
            'projects_count' => $this->projects()->count(),
            'clients_count' => $this->getActiveClientCount(),
            'api_calls_this_month' => $this->getApiCallsThisMonth(),
            'storage_used_mb' => $this->getStorageUsed(),
            'last_updated' => now()->toISOString(),
        ];

        $this->update(['usage_stats' => $stats]);
    }

    /**
     * Obtient le nombre d'appels API ce mois-ci
     */
    protected function getApiCallsThisMonth(): int
    {
        // Cette méthode devrait être implémentée selon votre système de tracking des API
        return $this->usage_stats['api_calls_this_month'] ?? 0;
    }

    /**
     * Obtient l'espace de stockage utilisé en MB
     */
    protected function getStorageUsed(): int
    {
        // Cette méthode devrait être implémentée selon votre système de stockage
        return $this->usage_stats['storage_used_mb'] ?? 0;
    }

    /**
     * Vérifie si la licence du tenant est expirée
     */
    public function isLicenceExpired(): bool
    {
        return $this->licence_expires_at !== null && $this->licence_expires_at->isPast();
    }

    /**
     * Obtient le statut de la licence
     */
    public function getLicenceStatus(): string
    {
        if ($this->isLicenceExpired()) {
            return 'expired';
        }

        if ($this->serialKey && $this->serialKey->status !== 'active') {
            return $this->serialKey->status;
        }

        return 'active';
    }

    /**
     * Scope pour les tenants primaires
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope pour les tenants en mode SaaS
     */
    public function scopeSaasMode($query)
    {
        return $query->where('licence_mode', 'saas');
    }

    /**
     * Scope pour les tenants en mode mono-compte
     */
    public function scopeSingleMode($query)
    {
        return $query->where('licence_mode', 'single');
    }

    /**
     * Get the projects associated with the tenant.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the licence associated with the tenant.
     */
    public function licence()
    {
        return $this->belongsTo(SerialKey::class, 'licence_id');
    }
    
    /**
     * Get the serial keys associated with the tenant.
     */
    public function serialKeys()
    {
        return $this->hasMany(SerialKey::class);
    }

    /**
     * Get the support tickets associated with the tenant.
     */
    public function supportTickets()
    {
        return $this->hasManyThrough(SupportTicket::class, Client::class);
    }

    /**
     * Scope a query to only include active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include inactive tenants.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }
    
    /**
     * Get the users associated with this tenant.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user');
    }
    
    /**
     * Vérifie si le tenant est actif.
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
    
    /**
     * Vérifie si le tenant est suspendu.
     */
    public function isSuspended()
    {
        return $this->status === self::STATUS_SUSPENDED;
    }
    
    /**
     * Scope a query to only include suspended tenants.
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    /**
     * Check if the tenant is on a trial period.
     */
    public function isOnTrial()
    {
        return $this->subscription_status === self::SUBSCRIPTION_TRIAL && $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the tenant's subscription is active.
     */
    public function hasActiveSubscription()
    {
        return $this->subscription_status === self::SUBSCRIPTION_ACTIVE && 
               ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }
}