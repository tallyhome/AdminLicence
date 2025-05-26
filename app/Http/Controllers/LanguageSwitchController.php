<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class LanguageSwitchController extends Controller
{
    /**
     * Change la langue de l'application et redirige vers la page précédente
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        $locale = $request->input('locale', $request->query('locale'));
        $availableLocales = config('app.available_locales', ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja', 'ar', 'tr']);
        
        if (!in_array($locale, $availableLocales)) {
            $locale = config('app.fallback_locale', 'en');
        }
        
        // Définir la langue dans l'application
        App::setLocale($locale);
        
        // Stocker la langue en session
        Session::put('locale', $locale);
        Session::save();
        
        // Définir un cookie pour conserver la préférence de langue
        Cookie::queue('locale', $locale, 60 * 24 * 30); // Cookie valable 30 jours
        
        // Déterminer l'URL de redirection
        $redirect = $request->header('Referer');
        
        // Si l'URL de référence n'est pas disponible, rediriger vers la page d'accueil
        if (!$redirect || strpos($redirect, 'set-locale') !== false) {
            // Éviter les boucles de redirection
            if ($request->is('admin/*')) {
                $redirect = route('admin.dashboard');
            } else {
                $redirect = route('frontend.home');
            }
        }
        
        return Redirect::to($redirect);
    }
}
