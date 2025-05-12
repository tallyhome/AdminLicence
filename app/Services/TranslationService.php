<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class TranslationService
{
    /**
     * Liste des langues disponibles dans l'application
     *
     * @var array
     */
    protected $availableLocales;

    public function __construct()
    {
        $this->availableLocales = config('app.available_locales', ['en', 'fr']);
    }

    /**
     * Obtenir la liste des langues disponibles
     *
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->availableLocales;
    }

    /**
     * Vérifier si une langue est disponible
     *
     * @param string $locale
     * @return bool
     */
    public function isLocaleAvailable(string $locale): bool
    {
        return in_array($locale, $this->availableLocales);
    }

    /**
     * Définir la langue active
     *
     * @param string $locale
     * @return bool
     */
    public function setLocale(string $locale): bool
    {
        if ($this->isLocaleAvailable($locale)) {
            // Stocker la langue en session
            Session::put('locale', $locale);
            
            // Définir la langue de l'application
            App::setLocale($locale);
            
            // Mettre à jour la configuration
            Config::set('app.locale', $locale);
            
            // Vider le cache des traductions
            Cache::forget('translations.' . $locale);
            
            // Forcer la mise à jour de la session
            Session::save();
            
            return true;
        }
        
        return false;
    }

    /**
     * Obtenir la langue actuelle de l'application
     *
     * @return string
     */
    public function getLocale(): string
    {
        // D'abord vérifier la session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if ($this->isLocaleAvailable($locale)) {
                App::setLocale($locale);
                return $locale;
            }
        }
        
        // Sinon, utiliser la langue de l'application
        return App::getLocale();
    }

    /**
     * Obtenir le nom de la langue dans sa langue native
     *
     * @param string $locale
     * @return string
     */
    public function getLocaleName(string $locale): string
    {
        $names = [
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'nl' => 'Nederlands',
            'ru' => 'Русский',
            'zh' => '中文',
            'ja' => '日本語',
            'tr' => 'Türkçe',
            'ar' => 'العربية'
        ];

        return $names[$locale] ?? $locale;
    }

    /**
     * Obtenir les traductions pour la langue active
     *
     * @param string|null $locale
     * @return array
     */
    public function getTranslations(?string $locale = null): array
    {
        $locale = $locale ?? $this->getLocale();
        
        // Récupérer les traductions du cache ou les charger
        $cacheKey = 'translations.' . $locale;
        
        return Cache::remember($cacheKey, 60 * 24, function () use ($locale) {
            $translations = [];
            
            // Charger le fichier principal de traduction
            $path = resource_path('locales/' . $locale . '/translation.json');
            if (File::exists($path)) {
                $translations = json_decode(File::get($path), true) ?? [];
            } else {
                // Fallback vers l'anglais si la traduction n'existe pas
                $fallbackPath = resource_path('locales/en/translation.json');
                if (File::exists($fallbackPath)) {
                    $translations = json_decode(File::get($fallbackPath), true) ?? [];
                }
            }
            
            // Charger les fichiers de traduction supplémentaires
            $additionalFiles = File::glob(resource_path('locales/' . $locale . '/*.json'));
            foreach ($additionalFiles as $file) {
                if (basename($file) !== 'translation.json') {
                    $key = pathinfo($file, PATHINFO_FILENAME);
                    $content = json_decode(File::get($file), true) ?? [];
                    $translations[$key] = $content;
                }
            }
            
            return $translations;
        });
    }

    /**
     * Traduire une clé
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->getLocale();
        
        // Récupérer les traductions du cache ou les charger
        $translations = $this->getTranslations($locale);
        
        // Gérer les clés imbriquées avec la notation point
        $keys = explode('.', $key);
        $translation = $translations;
        
        foreach ($keys as $k) {
            if (!isset($translation[$k])) {
                return $key;
            }
            $translation = $translation[$k];
        }
        
        // Si la traduction est un tableau, retourner la clé
        if (is_array($translation)) {
            return $key;
        }
        
        // Remplacer les variables
        if (!empty($replace)) {
            foreach ($replace as $key => $value) {
                $translation = str_replace(':' . $key, $value, $translation);
            }
        }
        
        return $translation;
    }

    /**
     * Obtenir la liste des langues disponibles avec leurs noms natifs
     *
     * @return array
     */
    public function getAvailableLanguages(): array
    {
        $languages = [];
        foreach ($this->availableLocales as $locale) {
            $languages[$locale] = $this->getLocaleName($locale);
        }
        return $languages;
    }
}