@extends('admin.layouts.app')

@section('title', 'Exemple d\'intégration Flutter')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Intégration de licence avec Flutter</h1>
                <a href="{{ route('admin.documentation.licence') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour à la documentation
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Exemple d'intégration avec Flutter</h5>
                </div>
                <div class="card-body">
                    <p>Voici comment intégrer le système de vérification de licence dans une application Flutter. Cet exemple inclut la gestion complète des différents statuts de licence (active, suspendue, révoquée, expirée).</p>
                    
                    <h5>1. Créer un service de licence</h5>
                    <pre><code class="language-dart">// lib/services/licence_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class LicenceService {
  // URL de l'API de vérification - utiliser le point d'entrée PHP direct qui est le plus fiable
  static const String API_URL = 'https://licence.votredomaine.com/api/check-serial.php';
  
  // Clé pour le stockage local
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
      
      // Vérifier le code de statut HTTP
      if (response.statusCode != 200) {
        return {
          'success': false,
          'message': 'Erreur HTTP: ${response.statusCode}',
          'donnees': null
        };
      }
      
      // Analyser la réponse JSON
      final Map<String, dynamic> resultat = json.decode(response.body);
      
      // Vérifier les différents statuts de la clé
      final Map<String, dynamic>? data = resultat['data'];
      final bool estValide = resultat['status'] == 'success';
      
      // Si la licence est valide, stocker les informations localement
      if (estValide && data != null) {
        // Vérifier si la licence n'est pas expirée, suspendue ou révoquée
        final bool estExpire = data['is_expired'] ?? false;
        final bool estSuspendu = data['is_suspended'] ?? false;
        final bool estRevoque = data['is_revoked'] ?? false;
        
        if (!estExpire && !estSuspendu && !estRevoque) {
          // Stocker la clé et les données de licence localement
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(LICENCE_KEY_PREF, cleSeriale);
          await prefs.setString(LICENCE_DATA_PREF, json.encode(data));
          
          // Définir une date d'expiration pour la vérification locale (7 jours)
          final DateTime validUntil = DateTime.now().add(Duration(days: 7));
          await prefs.setString(LICENCE_VALID_UNTIL_PREF, validUntil.toIso8601String());
        }
      }
      
      // Retourner le résultat complet
      return {
        'success': estValide,
        'message': resultat['message'] ?? 'Erreur inconnue',
        'donnees': data
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Erreur: $e',
        'donnees': null
      };
    }
  }
  
  // Méthode pour vérifier si une licence valide est stockée localement
  Future<bool> hasValidLocalLicence() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      
      // Vérifier si une clé de licence est stockée
      if (!prefs.containsKey(LICENCE_KEY_PREF) || 
          !prefs.containsKey(LICENCE_DATA_PREF) ||
          !prefs.containsKey(LICENCE_VALID_UNTIL_PREF)) {
        return false;
      }
      
      // Vérifier si la validation locale est encore valide
      final String? validUntilStr = prefs.getString(LICENCE_VALID_UNTIL_PREF);
      if (validUntilStr == null) return false;
      
      final DateTime validUntil = DateTime.parse(validUntilStr);
      if (DateTime.now().isAfter(validUntil)) {
        // La validation locale a expiré, il faut revérifier en ligne
        return false;
      }
      
      // Tout est valide localement
      return true;
    } catch (e) {
      return false;
    }
  }
  
  // Récupérer les données de licence stockées localement
  Future<Map<String, dynamic>?> getLocalLicenceData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final String? dataStr = prefs.getString(LICENCE_DATA_PREF);
      if (dataStr == null) return null;
      
      return json.decode(dataStr);
    } catch (e) {
      return null;
    }
  }
  
  // Récupérer la clé de licence stockée localement
  Future<String?> getStoredLicenceKey() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(LICENCE_KEY_PREF);
    } catch (e) {
      return null;
    }
  }
  
  // Effacer toutes les données de licence stockées localement
  Future<void> clearLicenceData() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(LICENCE_KEY_PREF);
      await prefs.remove(LICENCE_DATA_PREF);
      await prefs.remove(LICENCE_VALID_UNTIL_PREF);
    } catch (e) {
      // Ignorer les erreurs
    }
  }
}</code></pre>

                    <h5 class="mt-4">2. Créer un écran d'activation de licence</h5>
                    <pre><code class="language-dart">// lib/screens/licence_activation_screen.dart
import 'package:flutter/material.dart';
import '../services/licence_service.dart';

class LicenceActivationScreen extends StatefulWidget {
  @override
  _LicenceActivationScreenState createState() => _LicenceActivationScreenState();
}

class _LicenceActivationScreenState extends State<LicenceActivationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _licenceKeyController = TextEditingController();
  final _licenceService = LicenceService();
  
  bool _isActivating = false;
  String _message = '';
  bool _hasError = false;
  
  @override
  void initState() {
    super.initState();
    _checkExistingLicence();
  }
  
  Future<void> _checkExistingLicence() async {
    final hasValidLicence = await _licenceService.hasValidLocalLicence();
    if (hasValidLicence) {
      // Rediriger vers l'écran principal si une licence valide existe déjà
      WidgetsBinding.instance.addPostFrameCallback((_) {
        Navigator.of(context).pushReplacementNamed('/home');
      });
    }
  }
  
  Future<void> _activateLicence() async {
    if (!_formKey.currentState!.validate()) return;
    
    setState(() {
      _isActivating = true;
      _message = 'Vérification de la licence...';
      _hasError = false;
    });
    
    try {
      final String cleSeriale = _licenceKeyController.text.trim();
      
      // Appeler le service de vérification de licence
      final result = await _licenceService.verifierLicence(cleSeriale);
      
      // Analyser le résultat
      if (result['success']) {
        // Vérifier les données spécifiques de la licence
        final donnees = result['donnees'];
        final bool estExpire = donnees['is_expired'] ?? false;
        final bool estSuspendu = donnees['is_suspended'] ?? false;
        final bool estRevoque = donnees['is_revoked'] ?? false;
        final String? dateExpiration = donnees['expires_at'];
        
        setState(() {
          _isActivating = false;
          _hasError = estExpire || estSuspendu || estRevoque;
          
          if (estExpire) {
            _message = 'Cette licence a expiré le $dateExpiration.';
          } else if (estSuspendu) {
            _message = 'Cette licence est actuellement suspendue. Veuillez contacter le support.';
          } else if (estRevoque) {
            _message = 'Cette licence a été révoquée. Veuillez contacter le support.';
          } else {
            _message = 'Licence activée avec succès! Expire le $dateExpiration.';
            
            // Rediriger vers l'écran principal après activation réussie
            Future.delayed(Duration(seconds: 1), () {
              Navigator.of(context).pushReplacementNamed('/home');
            });
          }
        });
      } else {
        setState(() {
          _isActivating = false;
          _hasError = true;
          _message = result['message'] ?? 'Erreur inconnue lors de l\'activation.';
        });
      }
    } catch (e) {
      setState(() {
        _isActivating = false;
        _hasError = true;
        _message = 'Erreur: $e';
      });
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Activation de licence'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                'Entrez votre clé de licence',
                style: Theme.of(context).textTheme.headlineSmall,
              ),
              SizedBox(height: 16),
              TextFormField(
                controller: _licenceKeyController,
                decoration: InputDecoration(
                  labelText: 'Clé de licence',
                  hintText: 'Format: XXXX-XXXX-XXXX-XXXX',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return 'Veuillez entrer une clé de licence';
                  }
                  return null;
                },
              ),
              SizedBox(height: 24),
              ElevatedButton(
                onPressed: _isActivating ? null : _activateLicence,
                child: _isActivating
                    ? CircularProgressIndicator(color: Colors.white)
                    : Text('Activer la licence'),
                style: ElevatedButton.styleFrom(
                  padding: EdgeInsets.symmetric(vertical: 16),
                ),
              ),
              SizedBox(height: 24),
              if (_message.isNotEmpty)
                Container(
                  padding: EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: _hasError ? Colors.red[100] : Colors.green[100],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _message,
                    style: TextStyle(
                      color: _hasError ? Colors.red[900] : Colors.green[900],
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
  
  @override
  void dispose() {
    _licenceKeyController.dispose();
    super.dispose();
  }
}</code></pre>

                    <h5 class="mt-4">3. Créer un middleware de vérification de licence</h5>
                    <pre><code class="language-dart">// lib/middleware/licence_middleware.dart
import 'package:flutter/material.dart';
import '../services/licence_service.dart';

class LicenceMiddleware {
  static final LicenceService _licenceService = LicenceService();
  
  // Middleware pour vérifier la licence avant d'accéder à un écran protégé
  static Future<bool> checkLicence(BuildContext context) async {
    // Vérifier si une licence valide est stockée localement
    final hasValidLocalLicence = await _licenceService.hasValidLocalLicence();
    
    if (hasValidLocalLicence) {
      return true; // Autoriser l'accès
    }
    
    // Essayer de vérifier en ligne avec la clé stockée
    final storedKey = await _licenceService.getStoredLicenceKey();
    if (storedKey != null) {
      final result = await _licenceService.verifierLicence(storedKey);
      
      if (result['success']) {
        final donnees = result['donnees'];
        if (donnees != null) {
          final bool estExpire = donnees['is_expired'] ?? false;
          final bool estSuspendu = donnees['is_suspended'] ?? false;
          final bool estRevoque = donnees['is_revoked'] ?? false;
          
          if (!estExpire && !estSuspendu && !estRevoque) {
            return true; // Licence valide, autoriser l'accès
          }
        }
      }
    }
    
    // Aucune licence valide trouvée, rediriger vers l'écran d'activation
    Navigator.of(context).pushReplacementNamed('/licence');
    return false; // Bloquer l'accès
  }
  
  // Wrapper pour protéger un widget avec vérification de licence
  static Widget protectScreen(Widget screen) {
    return FutureBuilder<bool>(
      future: _licenceService.hasValidLocalLicence(),
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return Scaffold(
            body: Center(
              child: CircularProgressIndicator(),
            ),
          );
        }
        
        if (snapshot.data == true) {
          return screen; // Afficher l'écran protégé
        } else {
          // Rediriger vers l'écran d'activation
          WidgetsBinding.instance.addPostFrameCallback((_) {
            Navigator.of(context).pushReplacementNamed('/licence');
          });
          
          return Scaffold(
            body: Center(
              child: CircularProgressIndicator(),
            ),
          );
        }
      },
    );
  }
}</code></pre>

                    <h5 class="mt-4">4. Utilisation dans l'application</h5>
                    <pre><code class="language-dart">// lib/main.dart
import 'package:flutter/material.dart';
import 'screens/licence_activation_screen.dart';
import 'screens/home_screen.dart';
import 'middleware/licence_middleware.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Mon Application',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      initialRoute: '/splash',
      routes: {
        '/splash': (context) => SplashScreen(),
        '/licence': (context) => LicenceActivationScreen(),
        '/home': (context) => LicenceMiddleware.protectScreen(HomeScreen()),
        // Autres routes protégées...
      },
    );
  }
}

// Écran de démarrage pour vérifier la licence
class SplashScreen extends StatefulWidget {
  @override
  _SplashScreenState createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkLicence();
  }
  
  Future<void> _checkLicence() async {
    await Future.delayed(Duration(seconds: 1)); // Afficher le splash screen brièvement
    
    final hasValidLicence = await LicenceMiddleware.checkLicence(context);
    if (hasValidLicence) {
      Navigator.of(context).pushReplacementNamed('/home');
    }
    // Si pas de licence valide, le middleware redirigera automatiquement vers l'écran d'activation
  }
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Image.asset('assets/logo.png', width: 150),
            SizedBox(height: 24),
            CircularProgressIndicator(),
            SizedBox(height: 24),
            Text('Vérification de la licence...'),
          ],
        ),
      ),
    );
  }
}</code></pre>

                    <h5 class="mt-4">5. Ajouter les dépendances requises</h5>
                    <pre><code class="language-yaml"># pubspec.yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^0.13.5
  shared_preferences: ^2.0.15</code></pre>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note importante :</strong> Ce code prend en charge tous les états possibles d'une licence (active, suspendue, révoquée, expirée) et inclut un mécanisme de mise en cache pour fonctionner hors ligne pendant une période limitée.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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
<script>
    // Highlight.js pour la coloration syntaxique du code
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });
    });
</script>
@endpush
