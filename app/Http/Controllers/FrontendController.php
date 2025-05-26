<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class FrontendController extends Controller
{
    /**
     * Constructeur du contrôleur
     */
    public function __construct()
    {
        // Aucun middleware d'authentification n'est appliqué à ce contrôleur
    }

    /**
     * Affiche la page d'accueil
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Forcer l'affichage de la vue home sans passer par la page de connexion
        $content = view('home')->render();
        return response($content);
    }

    /**
     * Affiche la page des fonctionnalités
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function features()
    {
        return view('features');
    }

    /**
     * Affiche la page des tarifs
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function pricing()
    {
        return view('pricing');
    }

    /**
     * Affiche la page FAQ
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function faq()
    {
        return view('faq');
    }

    /**
     * Affiche la page de support
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function support()
    {
        return view('support');
    }

    /**
     * Affiche les CGV
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function cgv()
    {
        return view('cgv');
    }

    /**
     * Affiche la politique de confidentialité
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function privacy()
    {
        return view('privacy');
    }

    /**
     * Change la langue de l'application
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setLocale(Request $request)
    {
        $locale = $request->input('locale');
        $availableLocales = config('app.available_locales', ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru']);
        
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
        
        // Rediriger vers la page précédente
        return back()->withInput();
    }
}
