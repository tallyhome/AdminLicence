<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

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
        
        try {
            // Récupérer les traductions du cache ou les charger
            $cacheKey = 'translations.' . $locale;
            
            return Cache::remember($cacheKey, 60 * 24, function () use ($locale) {
                $translations = [];
                
                // Charger le fichier principal de traduction depuis resources/locales/
                $path = resource_path('locales/' . $locale . '/translation.json');
                
                // Vérifier si le fichier existe
                if (File::exists($path)) {
                    try {
                        $content = File::get($path);
                        $decoded = json_decode($content, true);
                        
                        // Vérifier si le JSON est valide
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $translations = $decoded;
                        } else {
                            Log::error("Erreur de décodage JSON pour {$locale}/translation.json: " . json_last_error_msg());
                            // Fallback vers l'anglais
                            $translations = $this->loadFallbackTranslations();
                        }
                    } catch (\Exception $e) {
                        Log::error("Exception lors du chargement des traductions pour {$locale}: " . $e->getMessage());
                        // Fallback vers l'anglais
                        $translations = $this->loadFallbackTranslations();
                    }
                } else {
                    // Fallback vers l'anglais si le fichier n'existe pas
                    $translations = $this->loadFallbackTranslations();
                }
                
                // Charger les fichiers de traduction supplémentaires dans le répertoire de la locale
                $localeDir = resource_path('locales/' . $locale);
                if (File::isDirectory($localeDir)) {
                    $files = File::files($localeDir);
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'json' && $file->getFilename() !== 'translation.json') {
                            $content = File::get($file->getPathname());
                            $decoded = json_decode($content, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $translations = array_merge($translations, $decoded);
                            }
                        }
                    }
                }
                
                return $translations;
            });
        } catch (\Exception $e) {
            Log::error("Exception globale lors du chargement des traductions: " . $e->getMessage());
            return [
                'common' => [
                    'dashboard' => 'Dashboard',
                    'save' => 'Save',
                    'cancel' => 'Cancel',
                    'delete' => 'Delete',
                    'edit' => 'Edit'
                ]
            ];
        }
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

    /**
     * Charge les traductions de fallback (anglais)
     *
     * @return array
     */
    protected function loadFallbackTranslations(): array
    {
        $fallbackLocale = config('app.fallback_locale', 'en');
        $fallbackPath = resource_path('locales/' . $fallbackLocale . '/translation.json');
        
        try {
            if (File::exists($fallbackPath)) {
                $content = File::get($fallbackPath);
                $decoded = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }
        } catch (\Exception $e) {
            Log::error("Exception lors du chargement des traductions de fallback: " . $e->getMessage());
        }
        
        // Si tout échoue, retourner un tableau vide
        return [];
    }

    /**
     * Force le chargement des traductions depuis les fichiers JSON dans le dossier resources/locales
     * 
     * @param string $locale
     * @return array
     */
    private function loadJsonTranslations($locale)
    {
        $path = resource_path('locales/' . $locale . '/translation.json');
        
        if (!File::exists($path)) {
            // Fallback to English if the requested locale doesn't exist
            $path = resource_path('locales/en/translation.json');
            if (!File::exists($path)) {
                return [];
            }
        }
        
        try {
            $content = File::get($path);
            $translations = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error for translations', [
                    'locale' => $locale,
                    'path' => $path,
                    'error' => json_last_error_msg()
                ]);
                return [];
            }
            
            return is_array($translations) ? $translations : [];
        } catch (Exception $e) {
            Log::error('Error loading translations', [
                'locale' => $locale,
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}