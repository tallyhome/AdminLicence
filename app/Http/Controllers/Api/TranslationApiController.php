<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TranslationApiController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Récupère les traductions pour une langue donnée
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTranslations(Request $request)
    {
        $locale = $request->query('locale', app()->getLocale());
        
        // Vérifier si la langue est disponible
        if (!$this->translationService->isLocaleAvailable($locale)) {
            $locale = config('app.fallback_locale', 'en');
        }
        
        // Récupérer les traductions
        $translations = $this->translationService->getTranslations($locale);
        
        // Vérifier et fusionner avec les fichiers spécifiques
        $specificFiles = [
            'admin_login' => resource_path("locales/{$locale}/admin_login.json"),
        ];
        
        foreach ($specificFiles as $key => $path) {
            if (File::exists($path)) {
                try {
                    $content = File::get($path);
                    $decoded = json_decode($content, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // Ajouter ou remplacer les traductions spécifiques
                        $translations[$key] = $decoded;
                    }
                } catch (\Exception $e) {
                    \Log::error("Erreur lors du chargement du fichier {$path}: " . $e->getMessage());
                }
            }
        }
        
        return response()->json($translations);
    }
}
