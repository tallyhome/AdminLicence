<?php

namespace App\Providers;

use App\Services\LicenceModeService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class LicenceModeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Enregistrer le service comme singleton
        $this->app->singleton(LicenceModeService::class, function ($app) {
            return new LicenceModeService();
        });
        
        // Créer un alias pour faciliter l'accès
        $this->app->alias(LicenceModeService::class, 'licence.mode');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Note: Les informations de mode sont maintenant partagées via InjectLicenceModeToViews middleware
        // pour éviter les conflits de variables
        
        // Enregistrer des directives Blade personnalisées
        $this->registerBladeDirectives();
    }
    
    /**
     * Enregistre les directives Blade personnalisées
     */
    protected function registerBladeDirectives(): void
    {
        // Directive pour vérifier le mode SaaS
        Blade::if('saas', function () {
            return app(LicenceModeService::class)->isSaasMode();
        });
        
        // Directive pour vérifier le mode mono-compte
        Blade::if('singleaccount', function () {
            return app(LicenceModeService::class)->isSingleAccountMode();
        });
        
        // Directive pour vérifier une fonctionnalité
        Blade::if('hasfeature', function ($feature) {
            return app(LicenceModeService::class)->hasFeature($feature);
        });
        
        // Directive pour vérifier si une limite est atteinte
        Blade::directive('limitreached', function ($expression) {
            return "<?php echo app(App\\Services\\LicenceModeService::class)->isLimitReached({$expression}) ? 'true' : 'false'; ?>";
        });
        
        // Directive pour afficher le mode actuel
        Blade::directive('licencemode', function () {
            return "<?php echo app(App\\Services\\LicenceModeService::class)->getCurrentMode(); ?>";
        });
        
        // Directive pour afficher le label du mode
        Blade::directive('licencemodelabel', function () {
            return "<?php 
                \$mode = app(App\\Services\\LicenceModeService::class)->getCurrentMode();
                echo \$mode === App\\Services\\LicenceModeService::MODE_SAAS ? 'SaaS' : 'Mono-compte';
            ?>";
        });
    }
}