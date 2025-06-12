<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    /**
     * Service de traduction
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
     * Récupère les traductions pour une langue donnée
     * Lit les fichiers dans /resources/locales/
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTranslations(Request $request)
    {
        try {
            Log::info('Début de getTranslations dans TranslationController');
            
            // Récupérer la langue demandée ou utiliser la langue par défaut
            $locale = $request->get('locale', app()->getLocale());
            Log::info('Locale demandée: ' . $locale);
            
            // Vérifier si la langue est disponible
            if (!$this->translationService->isLocaleAvailable($locale)) {
                $locale = config('app.fallback_locale', 'en');
                Log::info('Langue non disponible, utilisation du fallback: ' . $locale);
            }
            
            // Charger les traductions depuis le service
            Log::info('Tentative de chargement des traductions depuis le service pour: ' . $locale);
            $translations = $this->translationService->getTranslations($locale);
            
            // Si les traductions sont vides, essayer de charger directement le fichier JSON
            if (empty($translations)) {
                Log::info('Traductions vides, tentative de chargement direct du fichier JSON');
                $path = resource_path('locales/' . $locale . '/translation.json');
                Log::info('Chemin du fichier: ' . $path);
                
                if (File::exists($path)) {
                    Log::info('Le fichier existe, lecture du contenu');
                    $content = File::get($path);
                    $decoded = json_decode($content, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        Log::info('Décodage JSON réussi, utilisation des traductions du fichier');
                        $translations = $decoded;
                    } else {
                        Log::error('Erreur de décodage JSON: ' . json_last_error_msg());
                    }
                } else {
                    Log::warning('Le fichier de traduction n\'existe pas: ' . $path);
                }
            } else {
                Log::info('Traductions chargées avec succès depuis le service');
            }
            
            // Si toujours vide, utiliser le fallback
            if (empty($translations)) {
                Log::warning('Traductions toujours vides, utilisation du fallback');
                $fallbackLocale = config('app.fallback_locale', 'en');
                $path = resource_path('locales/' . $fallbackLocale . '/translation.json');
                Log::info('Chemin du fichier fallback: ' . $path);
                
                if (File::exists($path)) {
                    Log::info('Le fichier fallback existe, lecture du contenu');
                    $content = File::get($path);
                    $decoded = json_decode($content, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        Log::info('Décodage JSON du fallback réussi');
                        $translations = $decoded;
                    } else {
                        Log::error('Erreur de décodage JSON du fallback: ' . json_last_error_msg());
                    }
                } else {
                    Log::warning('Le fichier de traduction fallback n\'existe pas: ' . $path);
                }
            }
            
            // Vérifier si nous avons des traductions à retourner
            if (empty($translations)) {
                Log::warning('Aucune traduction disponible, utilisation d\'un tableau minimal');
                $translations = [
                    'common' => [
                        'dashboard' => 'Dashboard',
                        'save' => 'Save',
                        'cancel' => 'Cancel',
                        'delete' => 'Delete',
                        'edit' => 'Edit'
                    ]
                ];
            } else {
                Log::info('Traductions prêtes à être retournées');
            }
            
            // Ajouter des headers CORS pour permettre l'accès depuis n'importe quelle origine
            Log::info('Envoi de la réponse JSON avec headers CORS');
            return response()->json($translations)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
        } catch (\Exception $e) {
            Log::error('Exception dans TranslationController::getTranslations: ' . $e->getMessage());
            Log::error('Fichier: ' . $e->getFile() . ' ligne ' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            // En cas d'erreur, retourner un tableau minimal de traductions
            $translations = [
                'common' => [
                    'dashboard' => 'Dashboard',
                    'save' => 'Save',
                    'cancel' => 'Cancel',
                    'delete' => 'Delete',
                    'edit' => 'Edit'
                ],
                'error_info' => [
                    'message' => 'Une erreur est survenue lors du chargement des traductions',
                    'error_type' => get_class($e)
                ]
            ];
            
            Log::info('Envoi d\'une réponse JSON minimale avec headers CORS');
            return response()->json($translations)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
        }
    }
}
