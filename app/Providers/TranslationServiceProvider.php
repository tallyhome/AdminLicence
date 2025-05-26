<?php

namespace App\Providers;

use App\Services\TranslationService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TranslationService::class, function ($app) {
            return new TranslationService();
        });
        
        // Enregistrer le service comme un alias pour faciliter l'accès
        $this->app->alias(TranslationService::class, 'translation.service');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Charger les traductions depuis les fichiers JSON
        $this->loadTranslations();
        
        // Définir la locale de l'application
        $this->setLocale();
    }
    
    /**
     * Charge les traductions depuis les fichiers JSON
     */
    protected function loadTranslations(): void
    {
        // Définir les chemins des traductions
        $langPath = resource_path('locales');
        
        if (File::exists($langPath)) {
            // Ajouter le chemin des traductions JSON
            $this->app['translator']->addJsonPath($langPath);
            
            // Précharger les traductions pour la locale actuelle
            $locale = App::getLocale();
            $translationService = $this->app->make(TranslationService::class);
            $translationService->getTranslations($locale);
        }
    }
    
    /**
     * Définit la locale de l'application
     */
    protected function setLocale(): void
    {
        // Récupérer la locale depuis la session ou utiliser celle par défaut
        $locale = session('locale', config('app.locale'));
        
        // Vérifier si la locale est disponible
        $translationService = $this->app->make(TranslationService::class);
        if ($translationService->isLocaleAvailable($locale)) {
            App::setLocale($locale);
        }
    }
}