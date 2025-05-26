<?php

namespace App\Http\Middleware;

use App\Services\TranslationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class LocaleMiddleware
{
    /**
     * Le service de traduction
     *
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Constructeur
     *
     * @param TranslationService $translationService
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.available_locales', ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru']);
        
        // Vérifier si une langue est spécifiée dans le formulaire POST
        if ($request->isMethod('post') && $request->has('locale')) {
            $locale = $request->input('locale');
            if (in_array($locale, $availableLocales)) {
                // Configurer la locale dans l'application
                App::setLocale($locale);
                
                // Stocker en session
                Session::put('locale', $locale);
                
                // Forcer l'enregistrement de la session
                Session::save();
                
                // Créer un cookie durable
                Cookie::queue('locale', $locale, 60 * 24 * 30);
                
                // Log pour le débogage
                Log::debug('Locale changed to: ' . $locale);
            }
        }
        // Vérifier si la langue est spécifiée dans l'URL (via ?lang=xx)
        elseif ($request->has('lang')) {
            $locale = $request->get('lang');
            if (in_array($locale, $availableLocales)) {
                App::setLocale($locale);
                Session::put('locale', $locale);
                Session::save();
                Cookie::queue('locale', $locale, 60 * 24 * 30);
                Log::debug('Locale set from URL param: ' . $locale);
            }
        }
        // Vérifier si la langue est spécifiée dans la session
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
            if (in_array($locale, $availableLocales)) {
                App::setLocale($locale);
                Log::debug('Locale set from session: ' . $locale);
            }
        }
        // Vérifier si la langue est spécifiée dans un cookie
        elseif ($request->hasCookie('locale')) {
            $locale = $request->cookie('locale');
            if (in_array($locale, $availableLocales)) {
                App::setLocale($locale);
                Session::put('locale', $locale);
                Log::debug('Locale set from cookie: ' . $locale);
            }
        }
        
        // Forcer la locale à être disponible dans toutes les vues
        view()->share('currentLocale', App::getLocale());
        view()->share('availableLocales', $availableLocales);
        
        // Force le chargement des traductions JSON
        $locale = App::getLocale();
        $this->translationService->loadJsonTranslations($locale);
        
        // Si c'est une requête AJAX, ne pas interférer avec la réponse
        if ($request->ajax()) {
            return $next($request);
        }
        
        return $next($request);
    }
}