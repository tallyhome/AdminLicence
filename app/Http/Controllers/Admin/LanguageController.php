<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TranslationService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Change la langue de l'interface d'administration
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLanguage(Request $request)
    {
        $locale = $request->input('locale');
        $availableLocales = config('app.available_locales', ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru']);
        
        if (!in_array($locale, $availableLocales)) {
            return redirect()->back()->with('error', t('language.invalid_locale'));
        }

        try {
            // Définir la locale dans l'application
            app()->setLocale($locale);
            
            // Stocker en session
            session()->put('locale', $locale);
            session()->save();
            
            // Créer un cookie durable (30 jours)
            Cookie::queue('locale', $locale, 60 * 24 * 30);
            
            // Log pour le débogage
            Log::debug('Langue changée en: ' . $locale);
            
            // Forcer la régénération de la session pour éviter les problèmes de cache
            $request->session()->regenerate();
            
            // Vérifier si l'URL précédente est disponible
            $previousUrl = url()->previous();
            $baseUrl = url('/');
            
            // Si l'URL précédente n'est pas disponible ou pointe vers la même page
            // rediriger vers la page d'accueil admin pour éviter les boucles
            if (empty($previousUrl) || $previousUrl === $request->fullUrl()) {
                return redirect('/admin')->with('success', t('language.changed_successfully'));
            }
            
            // Rediriger vers la page précédente
            return redirect()->back()->with('success', t('language.changed_successfully'));
        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de langue: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors du changement de langue');
        }
    }
}