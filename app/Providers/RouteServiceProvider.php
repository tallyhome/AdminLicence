<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';  // Redirection vers la page d'accueil du frontend

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Nous supprimons la définition explicite de la route racine pour éviter les conflits
        // avec celle définie dans frontend.php
        
        // Nous commentons cette définition de route car elle est déjà définie dans web.php
        // et cause une boucle de redirection
        // Route::get('/login', function () {
        //     return redirect()->route('admin.login');
        // })->name('login');

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Charger les routes admin en premier avec leur préfixe
            Route::middleware('web')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['web', 'auth:admin'])
                ->prefix('admin')
                ->group(base_path('routes/billing.php'));

            // Charger les routes frontend après les routes admin
            Route::middleware('frontend')
                ->group(base_path('routes/frontend.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
                

        });
    }
}
