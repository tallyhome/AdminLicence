

<?php $__env->startSection('title', 'Documentation API'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Documentation API</h1>
            <p class="lead">Cette documentation vous aidera à intégrer l'API de validation des licences dans vos applications.</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Table des matières</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#introduction">Introduction</a></li>
                        <li class="mb-2"><a href="#authentification">Authentification</a></li>
                        <li class="mb-2"><a href="#endpoints">Endpoints disponibles</a>
                            <ul>
                                <li><a href="#endpoint-check-serial">Vérification d'une clé de série</a></li>
                                <li><a href="#endpoint-secure-code">Récupération du code dynamique sécurisé</a></li>
                            </ul>
                        </li>
                        <li class="mb-2"><a href="#approches">Approches d'intégration</a></li>
                        <li class="mb-2"><a href="#exemples">Exemples d'intégration</a>
                            <ul>
                                <li><a href="#exemple-php">PHP Standard</a></li>
                                <li><a href="#exemple-laravel">Laravel</a></li>
                                <li><a href="#exemple-javascript">JavaScript</a></li>
                                <li><a href="#exemple-flutter">Flutter/Dart</a></li>
                            </ul>
                        </li>
                        <li class="mb-2"><a href="#bonnes-pratiques">Bonnes pratiques</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4" id="introduction">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Introduction</h5>
                </div>
                <div class="card-body">
                    <p>L'API AdminLicence vous permet de valider les licences de vos utilisateurs directement depuis vos applications. Elle offre des endpoints sécurisés pour vérifier la validité des clés de série et obtenir des codes dynamiques pour renforcer la sécurité.</p>
                    <p>Toutes les requêtes doivent être effectuées via HTTPS pour garantir la sécurité des données transmises.</p>
                </div>
            </div>

            <div class="card mb-4" id="authentification">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Authentification</h5>
                </div>
                <div class="card-body">
                    <p>Pour utiliser l'API, vous devez disposer d'une clé API valide. Vous pouvez créer et gérer vos clés API dans la section <a href="<?php echo e(route('admin.api-keys.index')); ?>">Clés API</a> de l'interface d'administration.</p>
                    <p>Pour les endpoints qui nécessitent une authentification, vous devez inclure votre clé API dans l'en-tête de la requête :</p>
                    <pre><code>Authorization: Bearer VOTRE_CLE_API</code></pre>
                </div>
            </div>

            <div class="card mb-4" id="endpoints">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Endpoints disponibles</h5>
                </div>
                <div class="card-body">
                    <h6 class="mt-3" id="endpoint-check-serial">1. Vérification d'une clé de série</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 150px">URL</th>
                                    <td><code>/api/v1/check-serial</code></td>
                                </tr>
                                <tr>
                                    <th>Méthode</th>
                                    <td><code>POST</code></td>
                                </tr>
                                <tr>
                                    <th>Authentification</th>
                                    <td>Non requise</td>
                                </tr>
                                <tr>
                                    <th>Paramètres</th>
                                    <td>
                                        <ul class="mb-0">
                                            <li><code>serial_key</code> (obligatoire) : La clé de série à vérifier</li>
                                            <li><code>domain</code> (optionnel) : Le domaine sur lequel la licence est utilisée</li>
                                            <li><code>ip_address</code> (optionnel) : L'adresse IP depuis laquelle la requête est effectuée</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Réponse</th>
                                    <td>
                                        <p>En cas de succès :</p>
                                        <pre><code>{
    "status": "success",
    "message": "Clé de série valide",
    "data": {
        "token": "jwt_token_pour_authentification",
        "project": "nom_du_projet",
        "expires_at": "2024-12-31T23:59:59.000000Z"
    }
}</code></pre>
                                        <p>En cas d'erreur :</p>
                                        <pre><code>{
    "status": "error",
    "message": "Message d'erreur spécifique"
}</code></pre>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-4" id="endpoint-secure-code">2. Récupération du code dynamique sécurisé</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 150px">URL</th>
                                    <td><code>/api/v1/get-secure-code</code></td>
                                </tr>
                                <tr>
                                    <th>Méthode</th>
                                    <td><code>GET</code></td>
                                </tr>
                                <tr>
                                    <th>Authentification</th>
                                    <td>JWT (obtenu lors de la vérification de la clé)</td>
                                </tr>
                                <tr>
                                    <th>Paramètres</th>
                                    <td>
                                        <ul class="mb-0">
                                            <li><code>token</code> (obligatoire) : Le token JWT obtenu lors de la vérification de la clé</li>
                                            <li><code>serial_key</code> (obligatoire) : La clé de série associée</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Réponse</th>
                                    <td>
                                        <p>En cas de succès :</p>
                                        <pre><code>{
    "status": "success",
    "data": {
        "secure_code": "code_dynamique_sécurisé",
        "valid_until": "2023-01-01T12:00:00.000000Z"
    }
}</code></pre>
                                        <p>En cas d'erreur :</p>
                                        <pre><code>{
    "status": "error",
    "message": "Message d'erreur spécifique"
}</code></pre>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4" id="approches">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Approches d'intégration</h5>
                </div>
                <div class="card-body">
                    <p>Plusieurs approches sont disponibles pour intégrer notre système de licence dans vos applications. Choisissez celle qui correspond le mieux à votre environnement de développement.</p>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Approche</th>
                                    <th>Avantages</th>
                                    <th>Cas d'utilisation recommandés</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>PHP Standard</strong></td>
                                    <td>
                                        <ul class="mb-0">
                                            <li>Compatible avec tous les projets PHP</li>
                                            <li>Aucune dépendance externe requise</li>
                                            <li>Facile à adapter à n'importe quelle architecture</li>
                                        </ul>
                                    </td>
                                    <td>Applications PHP sans framework spécifique, intégrations dans des CMS, projets legacy</td>
                                </tr>
                                <tr>
                                    <td><strong>Laravel</strong></td>
                                    <td>
                                        <ul class="mb-0">
                                            <li>Syntaxe plus élégante et concise</li>
                                            <li>Gestion automatique des erreurs HTTP</li>
                                            <li>Intégration facile avec le système de cache et de session</li>
                                        </ul>
                                    </td>
                                    <td>Applications Laravel, API RESTful modernes, projets utilisant l'écosystème Laravel</td>
                                </tr>
                                <tr>
                                    <td><strong>Flutter/Dart</strong></td>
                                    <td>
                                        <ul class="mb-0">
                                            <li>Support pour les applications mobiles</li>
                                            <li>Gestion asynchrone native</li>
                                            <li>Typage fort pour plus de sécurité</li>
                                        </ul>
                                    </td>
                                    <td>Applications mobiles iOS/Android, applications de bureau multi-plateformes</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4" id="exemples">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Exemples d'intégration</h5>
                </div>
                <div class="card-body">
                    <h6 id="exemple-php">Approche 1: PHP Standard (Compatible avec tous les projets)</h6>
                    <p class="text-muted mb-2">Cette approche utilise les fonctions PHP natives et est compatible avec n'importe quel projet PHP, indépendamment du framework utilisé.</p>
                    <pre><code>/**
 * Exemple d'intégration du système de licence avec PHP standard
 */
class LicenceValidator {
    private $apiUrl;
    private $serialKey;
    private $token;
    private $secureCode;
    private $validUntil;
    
    public function __construct(string $apiUrl, string $serialKey) {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->serialKey = $serialKey;
    }
    
    /**
     * Vérifie la validité de la clé de série
     */
    public function verifyLicence(string $domain = null, string $ipAddress = null): bool {
        $data = [
            'serial_key' => $this->serialKey
        ];
        
        if ($domain) {
            $data['domain'] = $domain;
        }
        
        if ($ipAddress) {
            $data['ip_address'] = $ipAddress;
        }
        
        $response = $this->makeApiRequest('/api/v1/check-serial', 'POST', $data);
        
        if ($response && $response['status'] === 'success') {
            $this->token = $response['data']['token'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Récupère le code sécurisé dynamique
     */
    public function getSecureCode(): ?string {
        if (!$this->token) {
            return null;
        }
        
        // Vérifier si le code actuel est encore valide
        if ($this->secureCode && $this->validUntil && strtotime($this->validUntil) > time()) {
            return $this->secureCode;
        }
        
        $data = [
            'token' => $this->token,
            'serial_key' => $this->serialKey
        ];
        
        $response = $this->makeApiRequest('/api/v1/get-secure-code', 'GET', $data);
        
        if ($response && $response['status'] === 'success') {
            $this->secureCode = $response['data']['secure_code'];
            $this->validUntil = $response['data']['valid_until'];
            return $this->secureCode;
        }
        
        return null;
    }
    
    /**
     * Effectue une requête vers l'API
     */
    private function makeApiRequest(string $endpoint, string $method, array $data) {
        $url = $this->apiUrl . $endpoint;
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => $method,
                'content' => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === false) {
            return null;
        }
        
        return json_decode($result, true);
    }
}

// Exemple d'utilisation
$validator = new LicenceValidator('https://licence.exemple.com', 'ABCD-EFGH-IJKL-MNOP');

// Vérifier la licence
if ($validator->verifyLicence('monsite.com')) {
    echo "Licence valide!\n";
    
    // Récupérer le code sécurisé
    $secureCode = $validator->getSecureCode();
    if ($secureCode) {
        echo "Code sécurisé: " . $secureCode . "\n";
        // Utiliser ce code pour débloquer des fonctionnalités
    }
}</code></pre>

                    <h6 class="mt-4" id="exemple-laravel">Approche 2: Laravel (Intégration simplifiée)</h6>
                    <p class="text-muted mb-2">Cette approche utilise le client HTTP de Laravel pour une intégration plus simple et élégante dans les projets Laravel.</p>
                    <pre><code>use Illuminate\Support\Facades\Http;

// Vérification de la clé de série
$response = Http::post('https://votre-domaine.com/api/v1/check-serial', [
    'serial_key' => 'XXXX-XXXX-XXXX-XXXX',
    'domain' => 'example.com',
    'ip_address' => request()->ip()
]);

if ($response->successful()) {
    $data = $response->json();
    $token = $data['data']['token'];
    
    // Stockage du token pour une utilisation ultérieure
    session(['licence_token' => $token]);
    
    // Récupération du code dynamique sécurisé
    $secureResponse = Http::get('https://votre-domaine.com/api/v1/get-secure-code', [
        'token' => $token,
        'serial_key' => 'XXXX-XXXX-XXXX-XXXX'
    ]);
    
    if ($secureResponse->successful()) {
        $secureCode = $secureResponse->json()['data']['secure_code'];
        // Utilisation du code sécurisé
    }
}</code></pre>

                    <h6 class="mt-4" id="exemple-javascript">JavaScript</h6>
                    <pre><code>// Vérification de la clé de série
async function validateLicence(serialKey, domain) {
    try {
        const response = await fetch('https://votre-domaine.com/api/v1/check-serial', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                serial_key: serialKey,
                domain: domain
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            // Stockage du token
            localStorage.setItem('licence_token', data.data.token);
            
            // Récupération du code sécurisé
            const secureResponse = await fetch(`https://votre-domaine.com/api/v1/get-secure-code?token=${data.data.token}&serial_key=${serialKey}`);
            const secureData = await secureResponse.json();
            
            if (secureData.status === 'success') {
                // Utilisation du code sécurisé
                return secureData.data.secure_code;
            }
        }
        
        return null;
    } catch (error) {
        console.error('Erreur lors de la validation de la licence:', error);
        return null;
    }
}</code></pre>

                    <h6 class="mt-4" id="exemple-flutter">Flutter/Dart (Applications mobiles)</h6>
                    <p class="text-muted mb-2">Cette approche permet d'intégrer le système de licence dans des applications mobiles développées avec Flutter.</p>
                    <pre><code>import 'dart:convert';
import 'package:http/http.dart' as http;

class LicenceValidator {
  final String apiUrl;
  final String serialKey;
  String? token;
  String? secureCode;
  DateTime? validUntil;

  LicenceValidator(this.apiUrl, this.serialKey);

  /// Vérifie la validité de la clé de série
  Future<bool> verifyLicence({String? domain, String? ipAddress}) async {
    final Map<String, dynamic> data = {
      'serial_key': serialKey,
    };

    if (domain != null) {
      data['domain'] = domain;
    }

    if (ipAddress != null) {
      data['ip_address'] = ipAddress;
    }

    try {
      final response = await makeApiRequest('/api/v1/check-serial', 'POST', data);

      if (response != null && response['status'] == 'success') {
        token = response['data']['token'];
        return true;
      }

      return false;
    } catch (e) {
      print('Erreur lors de la vérification de la licence: $e');
      return false;
    }
  }

  /// Récupère le code sécurisé dynamique
  Future<String?> getSecureCode() async {
    if (token == null) {
      return null;
    }

    // Vérifier si le code actuel est encore valide
    if (secureCode != null && validUntil != null && validUntil!.isAfter(DateTime.now())) {
      return secureCode;
    }

    final Map<String, dynamic> data = {
      'token': token!,
      'serial_key': serialKey,
    };

    try {
      final response = await makeApiRequest('/api/v1/get-secure-code', 'GET', data);

      if (response != null && response['status'] == 'success') {
        secureCode = response['data']['secure_code'];
        validUntil = DateTime.parse(response['data']['valid_until']);
        return secureCode;
      }

      return null;
    } catch (e) {
      print('Erreur lors de la récupération du code sécurisé: $e');
      return null;
    }
  }

  /// Effectue une requête vers l'API
  Future<Map<String, dynamic>?> makeApiRequest(
      String endpoint, String method, Map<String, dynamic> data) async {
    final Uri url = Uri.parse('${apiUrl}${endpoint}');

    http.Response response;

    if (method == 'POST') {
      response = await http.post(
        url,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(data),
      );
    } else {
      response = await http.get(
        Uri.parse('$url?${Uri.encodeQueryComponent(jsonEncode(data))}'),
        headers: {'Content-Type': 'application/json'},
      );
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return jsonDecode(response.body);
    }

    return null;
  }
}

// Exemple d'utilisation
void main() async {
  final validator = LicenceValidator('https://licence.exemple.com', 'ABCD-EFGH-IJKL-MNOP');

  // Vérifier la licence
  final isValid = await validator.verifyLicence(domain: 'monapp.com');

  if (isValid) {
    print('Licence valide!');

    // Récupérer le code sécurisé
    final secureCode = await validator.getSecureCode();
    if (secureCode != null) {
      print('Code sécurisé: $secureCode');
      // Utiliser ce code pour débloquer des fonctionnalités
    }
  } else {
    print('Licence invalide ou expirée.');
  }
}</code></pre>
                </div>
            </div>

            <div class="card mb-4" id="bonnes-pratiques">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Bonnes pratiques</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li>Stockez les tokens JWT de manière sécurisée</li>
                        <li>Implémentez un mécanisme de rafraîchissement des tokens</li>
                        <li>Utilisez HTTPS pour toutes les communications avec l'API</li>
                        <li>Mettez en place un système de gestion des erreurs pour traiter les réponses d'erreur de l'API</li>
                        <li>Limitez l'accès à votre clé API et ne l'exposez jamais côté client</li>
                        <li>Implémentez un mécanisme de cache pour limiter les appels à l'API</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Support</h5>
                </div>
                <div class="card-body">
                    <p>Si vous rencontrez des problèmes lors de l'intégration de l'API ou si vous avez des questions, n'hésitez pas à contacter notre équipe de support technique.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    pre {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 0;
    }
    code {
        color: #e83e8c;
    }
    pre code {
        color: #212529;
    }
    .table-responsive {
        margin-bottom: 0;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/anchor-scroll.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/api-documentation.blade.php ENDPATH**/ ?>