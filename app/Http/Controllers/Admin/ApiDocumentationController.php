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
        $availableLanguages = $this->translationService->getAvailableLocales();
        $currentLanguage = $this->translationService->getLocale();

        return view('admin.api-documentation', [
            'availableLanguages' => $availableLanguages,
            'currentLanguage' => $currentLanguage
        ]);
    }

    public function licenceDocumentation()
    {
        $availableLanguages = $this->translationService->getAvailableLocales();
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
        
        $markdownPath = base_path('docs/FOURNISSEURS_EMAIL.md');
        $content = '';
        
        if (File::exists($markdownPath)) {
            $content = File::get($markdownPath);
        }
        
        return view('admin.email-documentation', [
            'availableLanguages' => $availableLanguages,
            'currentLanguage' => $currentLanguage,
            'content' => $content
        ]);
    }
}