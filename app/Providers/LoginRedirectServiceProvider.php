<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\UrlGenerator;

class LoginRedirectServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Remplacer la classe UrlGenerator par notre propre implémentation
        $this->app->extend('url', function ($urlGenerator, $app) {
            $routes = $app['router']->getRoutes();
            
            // Créer une classe anonyme qui étend UrlGenerator
            return new class($routes, $app->make('request')) extends UrlGenerator {
                public function route($name, $parameters = [], $absolute = true)
                {
                    // Rediriger les routes spécifiques vers leurs équivalents admin
                    if ($name === 'login') {
                        return '/admin/login';
                    }
                    
                    // Rediriger vers le tableau de bord admin si la route n'existe pas
                    try {
                        return parent::route($name, $parameters, $absolute);
                    } catch (\Exception $e) {
                        return route('admin.dashboard');
                    }
                    
                    return parent::route($name, $parameters, $absolute);
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Définir explicitement la route 'login' qui redirige vers 'admin.login'
        if (!Route::has('login')) {
            Route::get('/login', function () {
                return redirect()->to('/admin/login');
            })->name('login');
        }
    }
}