<?php

namespace App\Services;

use App\Models\SerialKey;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenceModeService
{
    /**
     * Modes de fonctionnement de l'application
     */
    const MODE_SINGLE_ACCOUNT = 'single_account';
    const MODE_SAAS = 'saas';
    
    /**
     * Cache key pour le mode de licence
     */
    const CACHE_KEY = 'app_licence_mode';
    
    /**
     * Durée de cache en minutes
     */
    const CACHE_DURATION = 60;
    
    /**
     * Détermine le mode de fonctionnement de l'application
     * basé sur les licences actives
     *
     * @return string
     */
    public function getCurrentMode(): string
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            return $this->detectMode();
        });
    }
    
    /**
     * Détecte le mode basé sur les licences actives
     *
     * @return string
     */
    protected function detectMode(): string
    {
        try {
            // Vérifier s'il y a des licences multi-comptes actives
            $hasMultiLicence = SerialKey::where('status', 'active')
                ->where('licence_type', SerialKey::LICENCE_TYPE_MULTI)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists();
            
            if ($hasMultiLicence) {
                Log::info('Mode SaaS détecté - Licence multi-comptes active trouvée');
                return self::MODE_SAAS;
            }
            
            // Vérifier s'il y a des licences single actives
            $hasSingleLicence = SerialKey::where('status', 'active')
                ->where('licence_type', SerialKey::LICENCE_TYPE_SINGLE)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists();
            
            if ($hasSingleLicence) {
                Log::info('Mode mono-compte détecté - Licence single active trouvée');
                return self::MODE_SINGLE_ACCOUNT;
            }
            
            // Par défaut, mode mono-compte si aucune licence n'est trouvée
            Log::warning('Aucune licence active trouvée - Mode mono-compte par défaut');
            return self::MODE_SINGLE_ACCOUNT;
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la détection du mode de licence: ' . $e->getMessage());
            return self::MODE_SINGLE_ACCOUNT;
        }
    }
    
    /**
     * Vérifie si l'application est en mode SaaS
     *
     * @return bool
     */
    public function isSaasMode(): bool
    {
        return $this->getCurrentMode() === self::MODE_SAAS;
    }
    
    /**
     * Vérifie si l'application est en mode mono-compte
     *
     * @return bool
     */
    public function isSingleAccountMode(): bool
    {
        return $this->getCurrentMode() === self::MODE_SINGLE_ACCOUNT;
    }
    
    /**
     * Force la mise à jour du cache du mode
     *
     * @return string
     */
    public function refreshMode(): string
    {
        Cache::forget(self::CACHE_KEY);
        return $this->getCurrentMode();
    }
    
    /**
     * Obtient les fonctionnalités disponibles selon le mode
     *
     * @return array
     */
    public function getAvailableFeatures(): array
    {
        $mode = $this->getCurrentMode();
        
        $features = [
            'licence_management' => true,
            'project_management' => true,
            'api_access' => true,
            'basic_support' => true,
        ];
        
        if ($mode === self::MODE_SAAS) {
            $features = array_merge($features, [
                'multi_tenant' => true,
                'client_management' => true,
                'billing_management' => true,
                'subscription_management' => true,
                'advanced_analytics' => true,
                'white_label' => true,
                'custom_domains' => true,
                'advanced_support' => true,
            ]);
        } else {
            $features = array_merge($features, [
                'multi_tenant' => false,
                'client_management' => false,
                'billing_management' => false,
                'subscription_management' => false,
                'advanced_analytics' => false,
                'white_label' => false,
                'custom_domains' => false,
                'advanced_support' => false,
            ]);
        }
        
        return $features;
    }
    
    /**
     * Vérifie si une fonctionnalité est disponible
     *
     * @param string $feature
     * @return bool
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->getAvailableFeatures();
        return $features[$feature] ?? false;
    }
    
    /**
     * Obtient les limites selon le mode
     *
     * @return array
     */
    public function getLimits(): array
    {
        $mode = $this->getCurrentMode();
        
        if ($mode === self::MODE_SAAS) {
            return [
                'max_tenants' => null, // Illimité
                'max_clients_per_tenant' => null, // Illimité
                'max_projects' => null, // Illimité
                'max_serial_keys' => null, // Illimité
                'max_api_calls_per_minute' => 1000,
                'storage_limit_gb' => null, // Illimité
            ];
        } else {
            return [
                'max_tenants' => 1,
                'max_clients_per_tenant' => 1,
                'max_projects' => 50,
                'max_serial_keys' => 1000,
                'max_api_calls_per_minute' => 100,
                'storage_limit_gb' => 10,
            ];
        }
    }
    
    /**
     * Vérifie si une limite est atteinte
     *
     * @param string $limitType
     * @param int $currentValue
     * @return bool
     */
    public function isLimitReached(string $limitType, int $currentValue): bool
    {
        $limits = $this->getLimits();
        $limit = $limits[$limitType] ?? null;
        
        if ($limit === null) {
            return false; // Pas de limite
        }
        
        return $currentValue >= $limit;
    }
    
    /**
     * Obtient les routes disponibles selon le mode
     *
     * @return array
     */
    public function getAvailableRoutes(): array
    {
        $mode = $this->getCurrentMode();
        
        $baseRoutes = [
            'admin.dashboard',
            'admin.serial-keys.*',
            'admin.projects.*',
            'admin.settings.*',
            'admin.profile.*',
        ];
        
        if ($mode === self::MODE_SAAS) {
            return array_merge($baseRoutes, [
                'admin.tenants.*',
                'admin.clients.*',
                'admin.billing.*',
                'admin.subscriptions.*',
                'admin.analytics.*',
                'admin.support.*',
            ]);
        }
        
        return $baseRoutes;
    }
    
    /**
     * Vérifie si une route est accessible selon le mode
     *
     * @param string $routeName
     * @return bool
     */
    public function isRouteAccessible(string $routeName): bool
    {
        $availableRoutes = $this->getAvailableRoutes();
        
        foreach ($availableRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }
        
        return false;
    }
}