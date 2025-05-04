<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use App\Services\TranslationService;

class TranslationController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function index(Request $request)
    {
        $languages = $this->translationService->getAvailableLocales();
        $selectedLang = $request->query('lang', $languages[0]);
        $page = $request->query('page', 1);
        $perPage = 500; // Augmentation pour afficher plus de traductions
        
        $translations = [];
        $jsonPath = resource_path("locales/{$selectedLang}/translation.json");
        
        if (File::exists($jsonPath)) {
            $content = File::get($jsonPath);
            $data = json_decode($content, true);
            if ($data) {
                // S'assurer que toutes les sections sont présentes
                $requiredSections = [
                    'common', 'layout', 'auth', 'dashboard', 'pagination', 
                    'projects', 'serial_keys', 'api', 'email', 'subscription', 
                    'language', 'install', 'translations', 'validation'
                ];
                
                foreach ($requiredSections as $section) {
                    if (!isset($data[$section])) {
                        $data[$section] = [];
                    }
                }
                
                $flattenedData = $this->flattenArray($data);
                $translations = collect($flattenedData)
                    ->map(function ($value, $key) use ($selectedLang) {
                        return [
                            'key' => $key,
                            'value' => $value,
                            'lang' => $selectedLang
                        ];
                    })
                    ->values();
            }
        }
        
        return view('admin.translations.index', [
            'languages' => $languages,
            'translations' => $translations,
            'currentPage' => 1,
            'perPage' => count($translations)
        ]);
    }

    protected function flattenArray(array $array, $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }

    public function update(Request $request)
    {
        $request->validate([
            'lang' => 'required|string',
            'file' => 'required|string',
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        $lang = $request->input('lang');
        $file = $request->input('file');
        $key = $request->input('key');
        $value = $request->input('value');

        $langPath = lang_path($lang . '/' . $file . '.php');
        
        if (File::exists($langPath)) {
            $translations = require $langPath;
            $translations[$key] = $value;
            
            $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
            File::put($langPath, $content);

            return response()->json(['message' => t('messages.translation_updated')]);
        }

        return response()->json(['error' => t('messages.translation_file_not_found')], 404);
    }

    public function create(Request $request)
    {
        $request->validate([
            'lang' => 'required|string',
            'file' => 'required|string',
        ]);

        $lang = $request->input('lang');
        $file = $request->input('file');

        $langPath = lang_path($lang);
        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);
        }

        $filePath = $langPath . '/' . $file . '.php';
        if (!File::exists($filePath)) {
            $content = "<?php\n\nreturn [];\n";
            File::put($filePath, $content);
            
            return response()->json(['message' => t('messages.translation_file_created')]);
        }

        return response()->json(['error' => t('messages.translation_file_exists')], 400);
    }
}