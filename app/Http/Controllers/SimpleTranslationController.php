<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SimpleTranslationController extends Controller
{
    /**
     * Récupère les traductions pour une langue donnée
     * Version simplifiée sans dépendance au service de traduction
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTranslations(Request $request): JsonResponse
    {
        try {
            $locale = $request->get('locale', 'en');
            
            // Valider la locale
            $availableLocales = ['en', 'fr', 'es', 'de', 'it', 'pt', 'nl', 'ru'];
            if (!in_array($locale, $availableLocales)) {
                $locale = 'en';
            }
            
            // Charger les traductions depuis le fichier JSON public
            $translationFile = public_path("lang-{$locale}.json");
            
            if (!File::exists($translationFile)) {
                // Fallback vers l'anglais si le fichier n'existe pas
                $translationFile = public_path('lang-en.json');
                $locale = 'en';
            }
            
            $translations = [];
            if (File::exists($translationFile)) {
                $content = File::get($translationFile);
                $translations = json_decode($content, true) ?? [];
            }
            
            return response()->json([
                'status' => 'success',
                'locale' => $locale,
                'translations' => $translations,
                'count' => count($translations)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in SimpleTranslationController: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load translations',
                'locale' => $request->get('locale', 'en'),
                'translations' => []
            ], 500);
        }
    }
}