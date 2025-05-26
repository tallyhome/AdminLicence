<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    /**
     * Change la langue de l'application
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLocale(Request $request)
    {
        // Pour les requêtes GET, prendre le paramètre de l'URL
        if ($request->isMethod('get')) {
            $locale = $request->query('locale');
        } else {
            // Pour les requêtes POST, prendre le paramètre du formulaire
            $locale = $request->input('locale');
        }
        
        // Si aucune locale n'est spécifiée, utiliser la locale par défaut
        if (empty($locale)) {
            $locale = config('app.fallback_locale', 'en');
        }
        
        $availableLocales = config('app.available_locales', ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja', 'ar', 'tr']);
        
        if (!in_array($locale, $availableLocales)) {
            return redirect('/')->with('error', __('language.invalid_locale'));
        }

        // Définir la locale dans l'application
        app()->setLocale($locale);
        
        // Stocker en session
        session()->put('locale', $locale);
        session()->save();
        
        // Créer un cookie durable (30 jours)
        cookie()->queue('locale', $locale, 60 * 24 * 30);
        
        // Rediriger vers la page précédente ou la page d'accueil
        if ($request->hasHeader('referer')) {
            return back()->withInput();
        } else {
            return redirect('/');
        }
    }
}