

<?php $__env->startSection('title', 'Documentation des licences'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1><?php echo e(__('Documentation des licences')); ?></h1>
                <div class="language-selector">
                    <select class="form-select" onchange="window.location.href = '<?php echo e(route('admin.set.language')); ?>?lang=' + this.value">
                        <?php $__currentLoopData = $availableLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($code); ?>" <?php echo e($currentLanguage === $code ? 'selected' : ''); ?>>
                                <?php echo e($name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2><?php echo e(__('Guide d\'intégration des licences')); ?></h2>
                    <p><?php echo e(__('Cette documentation vous guidera à travers le processus d\'intégration du système de licence dans votre application.')); ?></p>
                    
                    <!-- Section Installation -->
                    <section class="mb-4">
                        <h3><?php echo e(__('Installation')); ?></h3>
                        <p><?php echo e(__('Instructions détaillées pour l\'installation et la configuration initiale.')); ?></p>
                        
                        <div class="mt-3">
                            <ul class="nav nav-tabs" id="installationTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="php-tab" data-bs-toggle="tab" data-bs-target="#php" type="button" role="tab">PHP Simple</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="laravel-tab" data-bs-toggle="tab" data-bs-target="#laravel" type="button" role="tab">Laravel</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="flutter-tab" data-bs-toggle="tab" data-bs-target="#flutter" type="button" role="tab">Flutter</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="installationTabsContent">
                                <!-- PHP Simple -->
                                <div class="tab-pane fade show active" id="php" role="tabpanel">
                                    <h5>Installation pour PHP</h5>
                                    <p>Pour intégrer le système de licence dans une application PHP standard :</p>
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
    // URL de l'API de vérification
    $url = "https://licence.votredomaine.com/api/check-serial.php";
    
    // Données à envoyer
    $donnees = [
        'serial_key' => $cleSeriale,
        'domain' => $domaine ?: $_SERVER['SERVER_NAME'],
        'ip_address' => $adresseIP ?: $_SERVER['REMOTE_ADDR']
    ];
    
    // Initialiser cURL
    $ch = curl_init($url);
    
    // Configurer cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($donnees));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout de 10 secondes
    
    // Exécuter la requête
    $reponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Décoder la réponse JSON
    $resultat = json_decode($reponse, true);
    
    // Préparer le résultat
    return [
        'valide' => ($httpCode == 200 && isset($resultat['status']) && $resultat['status'] === 'success'),
        'message' => $resultat['message'] ?? 'Erreur inconnue',
        'donnees' => $resultat['data'] ?? null
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
                                    <h5>Installation pour Laravel</h5>
                                    <p>Pour intégrer le système de licence dans une application Laravel :</p>
                                    
                                    <h6>1. Créez un Service Provider</h6>
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
                return redirect()->route('licence.error', ['message' => 'Clé de licence non configurée']);
            }
            
            // Vérifier si la licence est en cache
            if (Cache::has('licence_valide')) {
                return true;
            }
            
            // Préparer les données
            $donnees = [
                'serial_key' => $cleSeriale,
                'domain' => request()->getHost(),
                'ip_address' => request()->ip()
            ];
            
            // Faire la requête API
            $response = Http::post(config('licence.api_url'), $donnees);
            
            if ($response->successful() && $response->json('status') === 'success') {
                // Mettre en cache le résultat pour éviter trop de requêtes
                Cache::put('licence_valide', true, now()->addHours(24));
                return true;
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

                                    <h6>2. Créez un Service de Licence</h6>
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

                                    <h6>3. Créez un fichier de configuration</h6>
                                    <pre><code class="language-php">&lt;?php
// config/licence.php

return [
    'key' => env('LICENCE_KEY', ''),
    'api_url' => env('LICENCE_API_URL', 'https://licence.votredomaine.com/api/check-serial.php'),
];
</code></pre>

                                    <h6>4. Ajoutez le Service Provider dans config/app.php</h6>
                                    <pre><code class="language-php">'providers' => [
    // Autres providers...
    App\Providers\LicenceServiceProvider::class,
],
</code></pre>
                                </div>
                                
                                <!-- Flutter -->
                                <div class="tab-pane fade" id="flutter" role="tabpanel">
                                    <h5>Installation pour Flutter</h5>
                                    <p>Pour intégrer le système de licence dans une application Flutter :</p>
                                    
                                    <h6>1. Ajoutez les dépendances dans pubspec.yaml</h6>
                                    <pre><code class="language-yaml">dependencies:
  flutter:
    sdk: flutter
  http: ^0.13.5
  shared_preferences: ^2.0.15
</code></pre>

                                    <h6>2. Créez un service de licence</h6>
                                    <pre><code class="language-dart">import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class LicenceService {
  static const String API_URL = 'https://licence.votredomaine.com/api/check-serial.php';
  static const String LICENCE_KEY_PREF = 'licence_key';
  static const String LICENCE_VALID_PREF = 'licence_valid';
  static const String LICENCE_EXPIRY_PREF = 'licence_expiry';

  // Sauvegarder la clé de licence
  Future<bool> saveLicenceKey(String key) async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.setString(LICENCE_KEY_PREF, key);
  }

  // Récupérer la clé de licence
  Future<String?> getLicenceKey() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(LICENCE_KEY_PREF);
  }

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

                                    <h6>3. Exemple d'utilisation dans votre application</h6>
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
                        <h3><?php echo e(__('Vérification des licences')); ?></h3>
                        <p><?php echo e(__('Comment implémenter la vérification des licences dans votre application.')); ?></p>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Pour une sécurité optimale, nous recommandons de vérifier la licence à chaque démarrage de l'application et périodiquement pendant son utilisation.
                        </div>
                        
                        <h5>Bonnes pratiques</h5>
                        <ul>
                            <li>Stockez la clé de licence de manière sécurisée (fichier de configuration chiffré, base de données)</li>
                            <li>Mettez en cache le résultat de la vérification pour éviter trop de requêtes</li>
                            <li>Prévoyez un comportement dégradé en cas d'échec de connexion au serveur de licences</li>
                            <li>Implémentez une vérification périodique pour les applications à longue durée d'exécution</li>
                            <li>Utilisez HTTPS pour toutes les communications avec le serveur de licences</li>
                        </ul>
                    </section>

                    <!-- Section API -->
                    <section class="mb-4">
                        <h3><?php echo e(__('API de gestion des licences')); ?></h3>
                        <p><?php echo e(__('Documentation complète de l\'API de gestion des licences.')); ?></p>
                        
                        <p>Pour une documentation détaillée de l'API, veuillez consulter la <a href="<?php echo e(route('admin.api.documentation')); ?>">Documentation API</a>.</p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/anchor-scroll.js')); ?>"></script>
<script>
    // Highlight.js pour la coloration syntaxique du code
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/licence-documentation.blade.php ENDPATH**/ ?>