<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use App\Services\TranslationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

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
        
        $jsonPath = resource_path("locales/{$selectedLang}/translation.json");
        
        if (!File::exists($jsonPath)) {
            return view('admin.translations.index', [
                'languages' => $languages,
                'translations' => collect([]),
                'sections' => []
            ]);
        }

        try {
            // Lecture du fichier JSON
            $content = File::get($jsonPath);
            $data = json_decode($content, true) ?: [];
            
            // S'assurer que toutes les sections sont présentes
            $requiredSections = [
                'common', 'layout', 'auth', 'dashboard', 'pagination',
                'projects', 'serial_keys', 'api', 'email', 'subscription',
                'language', 'install', 'translations', 'validation',
                'messages', 'errors', 'permissions', 'settings'
            ];
            
            foreach ($requiredSections as $section) {
                if (!isset($data[$section])) {
                    $data[$section] = [];
                }
            }
            
            // Aplatir les données par section
            $translations = collect();
            foreach ($data as $section => $sectionData) {
                $flattenedSection = $this->flattenArray($sectionData, $section . '.');
                foreach ($flattenedSection as $key => $value) {
                    $translations->push([
                        'key' => $key,
                        'value' => $value,
                        'lang' => $selectedLang,
                        'section' => explode('.', $key)[0]
                    ]);
                }
            }
            
            // Grouper par section
            $groupedTranslations = $translations->groupBy('section');
            
            return view('admin.translations.index', [
                'languages' => $languages,
                'translations' => $translations,
                'sections' => $requiredSections,
                'groupedTranslations' => $groupedTranslations
            ]);
            
        } catch (\Exception $e) {
            report($e);
            return view('admin.translations.index', [
                'languages' => $languages,
                'translations' => collect([]),
                'currentPage' => 1,
                'totalPages' => 1,
                'perPage' => $perPage,
                'error' => 'Erreur lors du chargement des traductions'
            ]);
        }
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

            // Vider le cache des traductions
            Cache::forget("translations.{$lang}");
            Cache::forget("translations.{$lang}.{$file}");
            
            // Recharger les traductions
            $this->translationService->getTranslations($lang);

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

    public function store(Request $request)
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

        $path = resource_path("locales/{$lang}/{$file}.json");
        if (!file_exists($path)) {
            return response()->json(['error' => "Fichier de traduction introuvable."], 404);
        }

        $translations = json_decode(file_get_contents($path), true);
        // Supporte les clés imbriquées (ex: projects.create.title)
        $keys = explode('.', $key);
        $ref = &$translations;
        foreach ($keys as $k) {
            if (!isset($ref[$k])) $ref[$k] = [];
            $ref = &$ref[$k];
        }
        $ref = $value;

        file_put_contents($path, json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // Vider le cache des traductions
        Cache::forget("translations.{$lang}");
        Cache::forget("translations.{$lang}.{$file}");
        
        // Recharger les traductions
        $this->translationService->getTranslations($lang);

        return response()->json(['message' => 'Traduction mise à jour avec succès.']);
    }
}