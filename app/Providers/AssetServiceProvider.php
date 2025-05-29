<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\AssetHelper;

class AssetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Enregistrer les directives Blade pour les assets versionnés
        Blade::directive('versionedAsset', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::versionedAsset($expression); ?>";
        });
        
        Blade::directive('versionedJs', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::js($expression); ?>";
        });
        
        Blade::directive('versionedCss', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::css($expression); ?>";
        });
        
        Blade::directive('versionedImage', function ($expression) {
            return "<?php echo \App\Helpers\AssetHelper::image($expression); ?>";
        });
    }
}
