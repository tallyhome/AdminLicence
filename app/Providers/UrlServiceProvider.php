<?php

namespace App\Providers;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route as RouteFacade;

class UrlServiceProvider extends ServiceProvider
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
        // Créer une route 'login' qui redirige vers 'admin.login'
        if (!RouteFacade::has('login')) {
            RouteFacade::get('/login', function () {
                return redirect()->route('admin.login');
            })->name('login');
        }
    }
}
