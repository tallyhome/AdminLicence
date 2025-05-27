@extends('admin.layouts.app')

@section('title', t('licence_documentation.title'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>{{ t('licence_documentation.title') }}</h1>
                <div class="language-selector">
                    <form id="language-form" action="{{ route('admin.set.language') }}" method="POST" class="d-inline">
                        @csrf
                        <select class="form-select" name="locale" onchange="document.getElementById('language-form').submit()">
                            @foreach($availableLanguages as $code => $name)
                                <option value="{{ $code }}" {{ $currentLanguage === $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2>{{ t('licence_documentation.integration_guide.title') }}</h2>
                    <p>{{ t('licence_documentation.integration_guide.description') }}</p>
                    
                    <!-- Section Installation -->
                    <section class="mb-4">
                        <h3>{{ t('licence_documentation.installation.title') }}</h3>
                        <p>{{ t('licence_documentation.installation.description') }}</p>
                        
                        <div class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Nouveauté :</strong> Des exemples détaillés d'intégration sont disponibles pour tous les types de projets.
                                <ul class="mb-0 mt-2">
                                    <li><a href="{{ route('admin.examples.javascript') }}" class="text-decoration-underline">Exemple complet pour JavaScript</a> - Intégration dans des applications frontend et Node.js</li>
                                    <li><a href="{{ route('admin.examples.flutter') }}" class="text-decoration-underline">Exemple complet pour Flutter</a> - Intégration mobile avec vérification hors ligne</li>
                                </ul>
                            </div>
                            
                            <ul class="nav nav-tabs" id="installationTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="php-tab" data-bs-toggle="tab" data-bs-target="#php" type="button" role="tab">{{ t('licence_documentation.installation.tabs.php') }}</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="laravel-tab" data-bs-toggle="tab" data-bs-target="#laravel" type="button" role="tab">{{ t('licence_documentation.installation.tabs.laravel') }}</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="javascript-tab" data-bs-toggle="tab" data-bs-target="#javascript" type="button" role="tab">JavaScript</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="flutter-tab" data-bs-toggle="tab" data-bs-target="#flutter" type="button" role="tab">{{ t('licence_documentation.installation.tabs.flutter') }}</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="installationTabsContent">
                                <!-- PHP Simple -->
                                <div class="tab-pane fade show active" id="php" role="tabpanel">
                                    <h5>{{ t('licence_documentation.installation.php.title') }}</h5>
                                    <p>{{ t('licence_documentation.installation.php.description') }}</p>
                                    <pre><code class="language-php">&lt;?php
// Créez un fichier licence.php dans votre projet

/**
 * Fonction pour vérifier une licence
 * 
 * @param string $cleSeriale Clé de licence à vérifier
 * @param string $domaine Domaine du site (optionnel)
 * @param string $adresseIP Adresse IP du serveur (optionnel)
 * @return array Résultat de la vérification
 */
function verifierLicence($cleSeriale, $domaine = null, $adresseIP = null) {
    // URL de l'API de vérification - utiliser le point d'entrée PHP direct qui est le plus fiable
    $url = "https://licence.votredomaine.com/api/check-serial.php";
    
    // Données à envoyer
    $donnees = [
        'serial_key' => $cleSeriale,
        'domain' => $domaine ?: (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
        'ip_address' => $adresseIP ?: (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '')
    ];
    
    // Initialiser cURL
    $ch = curl_init($url);
    
    // Configurer cURL
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($donnees),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false // Désactiver pour le débogage seulement, activer en production
    ]);
    
    // Exécuter la requête
    $reponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erreur = curl_error($ch);
    
    // Fermer la session cURL
    curl_close($ch);
    
    // Traiter la réponse
    if ($erreur) {
        return ['success' => false, 'message' => 'Erreur de connexion: ' . $erreur];
    }
    
    $resultat = json_decode($reponse, true);
    
    // Vérifier les différents statuts de la clé
    $statut = $resultat['data']['status'] ?? null;
    $estExpire = $resultat['data']['is_expired'] ?? false;
    $estSuspendu = $resultat['data']['is_suspended'] ?? false;
    $estRevoque = $resultat['data']['is_revoked'] ?? false;
    $dateExpiration = $resultat['data']['expires_at'] ?? null;
    
    // Préparer les données de réponse avec plus d'informations
    $donnees = [
        'token' => $resultat['data']['token'] ?? null,
        'project' => $resultat['data']['project'] ?? null,
        'expires_at' => $dateExpiration,
        'status' => $statut,
        'is_expired' => $estExpire,
        'is_suspended' => $estSuspendu,
        'is_revoked' => $estRevoque
    ];
    
    return [
        'success' => ($httpCode == 200 && isset($resultat['status']) && $resultat['status'] == 'success'),
        'message' => $resultat['message'] ?? 'Erreur inconnue',
        'donnees' => $donnees
    ];
}

// Exemple d'utilisation dans votre application
function verifierAcces() {
    // Récupérer la clé de licence depuis la configuration ou la base de données
    $cleSeriale = 'XXXX-XXXX-XXXX-XXXX'; // Remplacez par votre méthode de stockage
    
    // Vérifier la licence
    $resultat = verifierLicence($cleSeriale);
    
    if (!$resultat['valide']) {
        // Licence invalide, limiter les fonctionnalités ou afficher un message
        echo "Erreur de licence: " . $resultat['message'];
        exit;
    }
    
    // Licence valide, continuer l'exécution normale
    return true;
}
</code></pre>
                                </div>
                                
                                <!-- Laravel -->
                                <div class="tab-pane fade" id="laravel" role="tabpanel">
                                    <h5>{{ t('licence_documentation.installation.laravel.title') }}</h5>
                                    <p>{{ t('licence_documentation.installation.laravel.description') }}</p>
                                    
                                    <h6>{{ t('licence_documentation.installation.laravel.step1') }}</h6>
                                    <pre><code class="language-php">&lt;?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('licence', function ($app) {
            return new \App\Services\LicenceService();
        });
    }

    public function boot()
    {
        // Vérifier la licence au démarrage de l'application
        if (!app()->runningInConsole() && !$this->isExcludedRoute()) {
            $this->verifierLicence();
        }
    }

    protected function isExcludedRoute()
    {
        $route = request()->path();
        $excludedRoutes = [
            'licence/activation',
            'licence/error',
            // Autres routes à exclure
        ];

        return in_array($route, $excludedRoutes);
    }

    protected function verifierLicence()
    {
        try {
            // Récupérer la clé de licence depuis la configuration
            $cleSeriale = config('licence.key');
            
            if (empty($cleSeriale)) {
                Log::warning('Clé de licence non configurée');
                return redirect()->route('licence.error', ['message' => 'Clé de licence non configurée']);
            }
            
            // Vérifier si la vérification est en cache
            if (Cache::has('licence_valide')) {
                return null; // Aucune redirection nécessaire, la licence est valide et en cache
            }
            
            // Utiliser le point d'entrée PHP direct qui est le plus fiable
            $response = Http::post('https://votre-domaine.com/api/check-serial.php', [
                'serial_key' => $cleSeriale,
                'domain' => request()->getHost(),
                'ip_address' => request()->ip()
            ]);
            
            // Vérifier si la requête a réussi
            if ($response->successful() && $response->json('status') === 'success') {
                // Récupérer les informations complètes de la licence
                $licenceData = $response->json('data');
                
                // Vérifier si la licence est expirée, suspendue ou révoquée
                $estExpire = $licenceData['is_expired'] ?? false;
                $estSuspendu = $licenceData['is_suspended'] ?? false;
                $estRevoque = $licenceData['is_revoked'] ?? false;
                
                if ($estExpire) {
                    Log::error('Licence expirée. Date d\'expiration: ' . ($licenceData['expires_at'] ?? 'Non définie'));
                    return redirect()->route('licence.error', ['message' => 'Votre licence a expiré']);
                }
                
                if ($estSuspendu) {
                    Log::error('Licence suspendue');
                    return redirect()->route('licence.error', ['message' => 'Votre licence est suspendue']);
                }
                
                if ($estRevoque) {
                    Log::error('Licence révoquée');
                    return redirect()->route('licence.error', ['message' => 'Votre licence a été révoquée']);
                }
                
                // Stocker le résultat en cache pendant 24 heures
                Cache::put('licence_valide', true, 60 * 60 * 24);
                Cache::put('licence_data', $licenceData, 60 * 60 * 24);
                return null; // Aucune redirection nécessaire
            }
            
            // Licence invalide
            Log::error('Erreur de licence: ' . $response->json('message'));
            return redirect()->route('licence.error', ['message' => $response->json('message')]);
            
        } catch (\Exception $e) {
            Log::error('Exception lors de la vérification de licence: ' . $e->getMessage());
            return redirect()->route('licence.error', ['message' => 'Erreur de connexion au serveur de licences']);
        }
    }
}
</code></pre>

                                    <h6>{{ t('licence_documentation.installation.laravel.step2') }}</h6>
                                    <pre><code class="language-php">&lt;?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicenceService
{
    protected $apiUrl;
    protected $licenceKey;
    
    public function __construct()
    {
        $this->apiUrl = config('licence.api_url');
        $this->licenceKey = config('licence.key');
    }
    
    /**
     * Vérifie si la licence est valide
     *
     * @return bool
     */
    public function estValide()
    {
        // Vérifier le cache d'abord
        if (Cache::has('licence_valide')) {
            return true;
        }
        
        try {
            $response = Http::post($this->apiUrl, [
                'serial_key' => $this->licenceKey,
                'domain' => request()->getHost(),
                'ip_address' => request()->ip()
            ]);
            
            $resultat = $response->json();
            
            if ($response->successful() && isset($resultat['status']) && $resultat['status'] === 'success') {
                Cache::put('licence_valide', true, now()->addHours(24));
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Erreur de vérification de licence: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les informations de la licence
     *
     * @return array|null
     */
    public function getInfos()
    {
        try {
            $response = Http::post($this->apiUrl, [
                'serial_key' => $this->licenceKey,
                'domain' => request()->getHost(),
                'ip_address' => request()->ip()
            ]);
            
            $resultat = $response->json();
            
            if ($response->successful() && isset($resultat['status']) && $resultat['status'] === 'success') {
                return $resultat['data'] ?? null;
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Erreur de récupération des infos de licence: ' . $e->getMessage());
            return null;
        }
    }
}
</code></pre>

                                    <h6>{{ t('licence_documentation.installation.laravel.step3') }}</h6>
                                    <pre><code class="language-php">&lt;?php
// config/licence.php

return [
    'key' => env('LICENCE_KEY', ''),
    'api_url' => env('LICENCE_API_URL', 'https://licence.votredomaine.com/api/check-serial.php'),
];
</code></pre>

                                    <h6>{{ t('licence_documentation.installation.laravel.step4') }}</h6>
                                    <pre><code class="language-php">'providers' => [
    // Autres providers...
    App\Providers\LicenceServiceProvider::class,
],
</code></pre>
                                </div>
                                
                                <!-- Flutter -->
                                <div class="tab-pane fade" id="flutter" role="tabpanel">
                                    <h5>{{ t('licence_documentation.installation.flutter.title') }}</h5>
                                    <p>{{ t('licence_documentation.installation.flutter.description') }}</p>
                                    
                                    <h6>{{ t('licence_documentation.installation.flutter.step1') }}</h6>
                                    <pre><code class="language-yaml">dependencies:
  flutter:
    sdk: flutter
  http: ^0.13.5
  shared_preferences: ^2.0.15
</code></pre>

                                    <h6>{{ t('licence_documentation.installation.flutter.step2') }}</h6>
                                    <pre><code class="language-dart">import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class LicenceService {
  // URL de l'API de vérification
  static const String API_URL = 'https://licence.votredomaine.com/api/check-serial.php';
  
  // Clés pour le stockage local
  static const String LICENCE_KEY_PREF = 'licence_key';
  static const String LICENCE_DATA_PREF = 'licence_data';
  static const String LICENCE_VALID_UNTIL_PREF = 'licence_valid_until';
  
  // Méthode pour vérifier une clé de licence
  Future<Map<String, dynamic>> verifierLicence(String cleSeriale, {String? domaine, String? adresseIP}) async {
    try {
      // Préparer les données à envoyer
      final Map<String, dynamic> donnees = {
        'serial_key': cleSeriale,
        'domain': domaine ?? 'flutter_app',
        'ip_address': adresseIP ?? '127.0.0.1'
      };
      
      // Effectuer la requête
      final response = await http.post(
        Uri.parse(API_URL),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode(donnees),
      );
      
      // Analyser la réponse
      final Map<String, dynamic> resultat = json.decode(response.body);
      
      return {
        'success': resultat['status'] == 'success',
        'message': resultat['message'] ?? 'Erreur inconnue',
        'donnees': resultat['data']
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur: $e',
        'donnees': null
      };
    }
  }
</code></pre>

                                    <div class="mt-3">
                                        <a href="{{ route('admin.examples.flutter') }}" class="btn btn-primary">
                                            <i class="fas fa-code me-2"></i> Voir l'exemple complet pour Flutter
                                        </a>
                                    </div>


  // Vérifier si la licence est valide
  Future<bool> isLicenceValid() async {
    final prefs = await SharedPreferences.getInstance();
    
    // Vérifier si nous avons déjà validé la licence récemment
    if (prefs.getBool(LICENCE_VALID_PREF) == true) {
      final expiryString = prefs.getString(LICENCE_EXPIRY_PREF);
      if (expiryString != null) {
        final expiry = DateTime.parse(expiryString);
        if (expiry.isAfter(DateTime.now())) {
          // La licence est encore valide selon le cache
          return true;
        }
      }
    }
    
    // Vérifier avec le serveur
    return await checkLicenceWithServer();
  }

  // Vérifier la licence avec le serveur
  Future<bool> checkLicenceWithServer() async {
    try {
      final licenceKey = await getLicenceKey();
      
      if (licenceKey == null || licenceKey.isEmpty) {
        return false;
      }
      
      final response = await http.post(
        Uri.parse(API_URL),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'serial_key': licenceKey,
          // Vous pouvez ajouter d'autres informations comme le domaine ou l'IP
        }),
      );
      
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        
        if (data['status'] == 'success') {
          // Sauvegarder le résultat dans les préférences
          final prefs = await SharedPreferences.getInstance();
          prefs.setBool(LICENCE_VALID_PREF, true);
          
          // Sauvegarder la date d'expiration
          if (data['data'] != null && data['data']['expires_at'] != null) {
            prefs.setString(LICENCE_EXPIRY_PREF, data['data']['expires_at']);
          } else {
            // Si pas de date d'expiration, mettre une date par défaut (24h)
            final expiry = DateTime.now().add(Duration(hours: 24));
            prefs.setString(LICENCE_EXPIRY_PREF, expiry.toIso8601String());
          }
          
          return true;
        }
      }
      
      return false;
      
    } catch (e) {
      print('Erreur lors de la vérification de la licence: $e');
      return false;
    }
  }

  // Activer une nouvelle licence
  Future<Map<String, dynamic>> activateLicence(String licenceKey) async {
    try {
      final response = await http.post(
        Uri.parse(API_URL),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'serial_key': licenceKey,
        }),
      );
      
      final data = jsonDecode(response.body);
      
      if (response.statusCode == 200 && data['status'] == 'success') {
        // Sauvegarder la clé de licence
        await saveLicenceKey(licenceKey);
        
        // Marquer comme valide
        final prefs = await SharedPreferences.getInstance();
        prefs.setBool(LICENCE_VALID_PREF, true);
        
        // Sauvegarder la date d'expiration
        if (data['data'] != null && data['data']['expires_at'] != null) {
          prefs.setString(LICENCE_EXPIRY_PREF, data['data']['expires_at']);
        }
        
        return {
          'success': true,
          'message': data['message'] ?? 'Licence activée avec succès',
          'data': data['data']
        };
      }
      
      return {
        'success': false,
        'message': data['message'] ?? 'Erreur d\'activation de la licence',
      };
      
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur de connexion: $e',
      };
    }
  }
}
</code></pre>

                                    <h6>{{ t('licence_documentation.installation.flutter.step3') }}</h6>
                                    <pre><code class="language-dart">import 'package:flutter/material.dart';
import 'licence_service.dart';

class LicenceActivationScreen extends StatefulWidget {
  @override
  _LicenceActivationScreenState createState() => _LicenceActivationScreenState();
}

class _LicenceActivationScreenState extends State<LicenceActivationScreen> {
  final _licenceService = LicenceService();
  final _licenceKeyController = TextEditingController();
  bool _isActivating = false;
  String _message = '';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Activation de licence')),
      body: Padding(
        padding: EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextField(
              controller: _licenceKeyController,
              decoration: InputDecoration(
                labelText: 'Clé de licence',
                hintText: 'Entrez votre clé de licence',
                border: OutlineInputBorder(),
              ),
            ),
            SizedBox(height: 16),
            ElevatedButton(
              onPressed: _isActivating ? null : _activateLicence,
              child: _isActivating 
                ? CircularProgressIndicator(valueColor: AlwaysStoppedAnimation<Color>(Colors.white))
                : Text('Activer'),
            ),
            SizedBox(height: 16),
            if (_message.isNotEmpty)
              Container(
                padding: EdgeInsets.all(12),
                color: _message.contains('succès') ? Colors.green[100] : Colors.red[100],
                child: Text(_message),
              ),
          ],
        ),
      ),
    );
  }

  Future<void> _activateLicence() async {
    final licenceKey = _licenceKeyController.text.trim();
    
    if (licenceKey.isEmpty) {
      setState(() {
        _message = 'Veuillez entrer une clé de licence';
      });
      return;
    }
    
    setState(() {
      _isActivating = true;
      _message = '';
    });
    
    try {
      final result = await _licenceService.activateLicence(licenceKey);
      
      setState(() {
        _isActivating = false;
        _message = result['message'];
      });
      
      if (result['success']) {
        // Rediriger vers l'écran principal après activation réussie
        Future.delayed(Duration(seconds: 1), () {
          Navigator.of(context).pushReplacementNamed('/home');
        });
      }
      
    } catch (e) {
      setState(() {
        _isActivating = false;
        _message = 'Erreur: $e';
      });
    }
  }
}
</code></pre>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Section Vérification -->
                    <section class="mb-4">
                        <h3>{{ t('licence_documentation.verification.title') }}</h3>
                        <p>{{ t('licence_documentation.verification.description') }}</p>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ t('licence_documentation.verification.security_tip') }}
                        </div>
                        
                        <h5>{{ t('licence_documentation.verification.best_practices.title') }}</h5>
                        <ul>
                            <li>{{ t('licence_documentation.verification.best_practices.item1') }}</li>
                            <li>{{ t('licence_documentation.verification.best_practices.item2') }}</li>
                            <li>{{ t('licence_documentation.verification.best_practices.item3') }}</li>
                            <li>{{ t('licence_documentation.verification.best_practices.item4') }}</li>
                            <li>{{ t('licence_documentation.verification.best_practices.item5') }}</li>
                        </ul>
                    </section>

                    <!-- Section API -->
                    <section class="mb-4">
                        <h3>{{ t('licence_documentation.api.title') }}</h3>
                        <p>{{ t('licence_documentation.api.description') }}</p>
                        
                        <p>{{ t('licence_documentation.api.link_text') }} <a href="{{ route('admin.api.documentation') }}">{{ t('licence_documentation.api.link_title') }}</a>.</p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .markdown-content h1 { font-size: 2rem; margin-bottom: 1rem; }
    .markdown-content h2 { font-size: 1.75rem; margin-top: 2rem; margin-bottom: 1rem; }
    .markdown-content h3 { font-size: 1.5rem; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .markdown-content h4 { font-size: 1.25rem; margin-top: 1.25rem; margin-bottom: 0.5rem; }
    .markdown-content p { margin-bottom: 1rem; }
    .markdown-content ul, .markdown-content ol { margin-bottom: 1rem; padding-left: 2rem; }
    .markdown-content table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; }
    .markdown-content table th, .markdown-content table td { padding: 0.5rem; border: 1px solid #dee2e6; }
    .markdown-content pre { background-color: #f8f9fa; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem; overflow-x: auto; }
    .markdown-content code { background-color: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-size: 0.875em; }
    .markdown-content pre code { padding: 0; background-color: transparent; }
    pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
    }
    code {
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/anchor-scroll.js') }}"></script>
<script>
    // Highlight.js pour la coloration syntaxique du code
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });
    });
</script>
@endpush