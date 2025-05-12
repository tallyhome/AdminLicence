<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TranslationService;
use Illuminate\Support\Facades\File;

class ApiDocumentationController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function index()
    {
        $availableLanguages = $this->translationService->getAvailableLanguages();
        $currentLanguage = $this->translationService->getLocale();

        return view('admin.api-documentation', [
            'availableLanguages' => $availableLanguages,
            'currentLanguage' => $currentLanguage
        ]);
    }

    public function licenceDocumentation()
    {
        $availableLanguages = $this->translationService->getAvailableLanguages();
        $currentLanguage = $this->translationService->getLocale();

        return view('admin.licence-documentation', [
            'availableLanguages' => $availableLanguages,
            'currentLanguage' => $currentLanguage
        ]);
    }
    
    public function emailDocumentation()
    {
        $availableLanguages = $this->translationService->getAvailableLocales();
        $currentLanguage = $this->translationService->getLocale();
        
        // Charger directement le contenu du fichier email_documentation.json
        $locale = $currentLanguage;
        $emailDocPath = resource_path('locales/' . $locale . '/email_documentation.json');
        
        // Si le fichier n'existe pas dans la langue actuelle, utiliser l'anglais comme fallback
        if (!File::exists($emailDocPath)) {
            $emailDocPath = resource_path('locales/en/email_documentation.json');
        }
        
        $emailDocContent = [];
        if (File::exists($emailDocPath)) {
            $emailDocContent = json_decode(File::get($emailDocPath), true) ?? [];
        }
        
        // Charger également la clé email_providers.title depuis le fichier de traduction principal
        $translationPath = resource_path('locales/' . $locale . '/translation.json');
        if (File::exists($translationPath)) {
            $translations = json_decode(File::get($translationPath), true) ?? [];
            if (isset($translations['email_providers']['title'])) {
                $emailDocContent['title'] = $translations['email_providers']['title'];
            }
        }
        
        return view('admin.email-documentation', [
            'availableLanguages' => $availableLanguages,
            'currentLanguage' => $currentLanguage,
            'emailDocContent' => $emailDocContent
        ]);
    }
}