@extends('admin.layouts.app')

@section('title', t('api_documentation.title'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>{{ t('api_documentation.title') }}</h1>
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
            <p class="lead">{{ t('api_documentation.description') }}</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ t('api_documentation.table_of_contents') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#introduction">{{ t('api_documentation.introduction') }}</a></li>
                        <li class="mb-2"><a href="#authentification">{{ t('api_documentation.authentication') }}</a></li>
                        <li class="mb-2"><a href="#endpoints">{{ t('api_documentation.available_endpoints') }}</a>
                            <ul>
                                <li><a href="#endpoint-check-serial">{{ t('api_documentation.endpoint_check_serial') }}</a></li>
                                <li><a href="#endpoint-secure-code">{{ t('api_documentation.endpoint_secure_code') }}</a></li>
                            </ul>
                        </li>
                        <li class="mb-2"><a href="#approches">{{ t('api_documentation.integration_approaches') }}</a></li>
                        <li class="mb-2"><a href="#exemples">{{ t('api_documentation.integration_examples') }}</a>
                            <ul>
                                <li><a href="#exemple-php">{{ t('api_documentation.php_standard') }}</a></li>
                                <li><a href="#exemple-laravel">Laravel</a></li>
                                <li><a href="#exemple-javascript">JavaScript</a></li>
                                <li><a href="#exemple-flutter">Flutter/Dart</a></li>
                            </ul>
                        </li>
                        <li class="mb-2"><a href="#bonnes-pratiques">
                            @php
                                // Récupérer la langue active
                                $translationService = app(\App\Services\TranslationService::class);
                                $locale = $translationService->getLocale();
                                
                                // Récupérer les traductions pour la langue active
                                $translations = $translationService->getTranslations($locale);
                                
                                // Déboguer les informations de langue et de traduction
                                $debug = false; // Mettre à true pour déboguer
                                if ($debug) {
                                    echo "Langue active: " . $locale . "<br>";
                                    if (isset($translations['api_documentation']['best_practices'])) {
                                        echo "Traduction trouvée: " . $translations['api_documentation']['best_practices'] . "<br>";
                                    } else {
                                        echo "Traduction non trouvée<br>";
                                    }
                                }
                                
                                // Afficher la traduction selon la langue
                                $bestPracticesText = 'Bonnes pratiques'; // Valeur par défaut en français
                                
                                // Vérifier si best_practices.title existe et l'utiliser
                                if (isset($translations['api_documentation']['best_practices']['title'])) {
                                    $bestPracticesText = $translations['api_documentation']['best_practices']['title'];
                                } else {
                                    // Fallback aux traductions codées en dur selon la langue
                                    if ($locale === 'en') {
                                        $bestPracticesText = 'Best Practices';
                                    } elseif ($locale === 'de') {
                                        $bestPracticesText = 'Best Practices';
                                    } elseif ($locale === 'es') {
                                        $bestPracticesText = 'Buenas prácticas';
                                    } elseif ($locale === 'it') {
                                        $bestPracticesText = 'Migliori pratiche';
                                    } elseif ($locale === 'pt') {
                                        $bestPracticesText = 'Boas práticas';
                                    } elseif ($locale === 'nl') {
                                        $bestPracticesText = 'Best practices';
                                    } elseif ($locale === 'ru') {
                                        $bestPracticesText = 'Лучшие практики';
                                    } elseif ($locale === 'zh') {
                                        $bestPracticesText = '最佳实践';
                                    } elseif ($locale === 'ja') {
                                        $bestPracticesText = 'ベストプラクティス';
                                    } elseif ($locale === 'tr') {
                                        $bestPracticesText = 'En iyi uygulamalar';
                                    } elseif ($locale === 'ar') {
                                        $bestPracticesText = 'أفضل الممارسات';
                                    }
                                }
                                
                                echo $bestPracticesText;
                            @endphp
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4" id="introduction">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ t('api_documentation.introduction') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ t('api_documentation.introduction_text') }}</p>
                    <p>{{ t('api_documentation.introduction_security') }}</p>
                </div>
            </div>

            <div class="card mb-4" id="authentification">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ t('api_documentation.authentication') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ t('api_documentation.authentication_text') }}</p>
                    <p>{{ t('api_documentation.authentication_header') }}</p>
                    <pre><code>Authorization: Bearer VOTRE_CLE_API</code></pre>
                </div>
            </div>

            <div class="card mb-4" id="endpoints">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ t('api_documentation.available_endpoints') }}</h5>
                </div>
                <div class="card-body">
                    <h6 class="mt-3" id="endpoint-check-serial">1. {{ t('api_documentation.endpoint_check_serial') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 150px">{{ t('api_documentation.endpoint_url') }}</th>
                                    <td><code>/api/v1/check-serial</code></td>
                                </tr>
                                <tr>
                                    <th>{{ t('api_documentation.endpoint_method') }}</th>
                                    <td><code>POST</code></td>
                                </tr>
                                <tr>
                                    <th>{{ t('api_documentation.endpoint_auth') }}</th>
                                    <td>{{ t('api_documentation.endpoint_optional') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ t('api_documentation.endpoint_params') }}</th>
                                    <td>
                                        <ul class="mb-0">
                                            <li><code>serial_key</code> ({{ t('api_documentation.endpoint_required') }}) : {{ t('api_documentation.serial_key_description') }}</li>
                                            <li><code>domain</code> ({{ t('api_documentation.endpoint_optional') }}) : {{ t('api_documentation.domain_description') }}</li>
                                            <li><code>ip_address</code> ({{ t('api_documentation.endpoint_optional') }}) : {{ t('api_documentation.ip_description') }}</li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-4" id="endpoint-secure-code">2. {{ t('api_documentation.endpoint_secure_code') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 150px">{{ t('api_documentation.endpoint_url') }}</th>
                                    <td><code>/api/v1/get-secure-code</code></td>
                                </tr>
                                <tr>
                                    <th>{{ t('api_documentation.endpoint_method') }}</th>
                                    <td><code>GET</code></td>
                                </tr>
                                <tr>
                                    <th>{{ t('api_documentation.endpoint_auth') }}</th>
                                    <td>JWT ({{ t('api_documentation.jwt_description') }})</td>
                                </tr>
                                <tr>
                                    <th>{{ t('api_documentation.endpoint_params') }}</th>
                                    <td>
                                        <ul class="mb-0">
                                            <li><code>token</code> ({{ t('api_documentation.endpoint_required') }}) : {{ t('api_documentation.token_description') }}</li>
                                            <li><code>serial_key</code> ({{ t('api_documentation.endpoint_required') }}) : {{ t('api_documentation.serial_key_description') }}</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ t('api_documentation.endpoint_response') }}</th>
                                    <td>
                                        <p>{{ t('api_documentation.endpoint_success') }}</p>
                                        <pre><code>{
    "status": "success",
    "data": {
        "secure_code": "code_dynamique_sécurisé",
        "valid_until": "2023-01-01T12:00:00.000000Z"
    }
}</code></pre>
                                        <p>{{ t('api_documentation.endpoint_error') }}</p>
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
                    <h5 class="mb-0">{{ t('api_documentation.integration_approaches') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ t('api_documentation.integration_text') }}</p>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ t('api_documentation.approach') }}</th>
                                    <th>{{ t('api_documentation.advantages') }}</th>
                                    <th>{{ t('api_documentation.recommended_use') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>{{ t('api_documentation.php_standard') }}</strong></td>
                                    <td>
                                        <ul class="mb-0">
                                            @php
                                                $advantages = t('api_documentation.php_advantages');
                                                if (is_array($advantages)) {
                                                    foreach($advantages as $advantage) {
                                                        echo '<li>' . $advantage . '</li>';
                                                    }
                                                }
                                            @endphp
                                        </ul>
                                    </td>
                                    <td>{{ t('api_documentation.php_use_case') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ t('api_documentation.laravel') }}</strong></td>
                                    <td>
                                        <ul class="mb-0">
                                            @php
                                                $advantages = t('api_documentation.laravel_advantages');
                                                if (is_array($advantages)) {
                                                    foreach($advantages as $advantage) {
                                                        echo '<li>' . $advantage . '</li>';
                                                    }
                                                }
                                            @endphp
                                        </ul>
                                    </td>
                                    <td>{{ t('api_documentation.laravel_use_case') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ t('api_documentation.javascript') }}</strong></td>
                                    <td>
                                        <ul class="mb-0">
                                            @php
                                                $advantages = t('api_documentation.javascript_advantages');
                                                if (is_array($advantages)) {
                                                    foreach($advantages as $advantage) {
                                                        echo '<li>' . $advantage . '</li>';
                                                    }
                                                }
                                            @endphp
                                        </ul>
                                    </td>
                                    <td>{{ t('api_documentation.javascript_use_case') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>{{ t('api_documentation.flutter') }}</strong></td>
                                    <td>
                                        <ul class="mb-0">
                                            @php
                                                $advantages = t('api_documentation.flutter_advantages');
                                                if (is_array($advantages)) {
                                                    foreach($advantages as $advantage) {
                                                        echo '<li>' . $advantage . '</li>';
                                                    }
                                                }
                                            @endphp
                                        </ul>
                                    </td>
                                    <td>{{ t('api_documentation.flutter_use_case') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4" id="exemples">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">{{ t('api_documentation.examples.title') }}</h5>
                </div>
                <div class="card-body">
                    <h6 id="exemple-php">{{ t('api_documentation.examples.php_standard.title') }}</h6>
                    <p class="text-muted mb-2">{{ t('api_documentation.examples.php_standard.description') }}</p>
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

                    <h6 class="mt-4" id="exemple-laravel">{{ t('api_documentation.examples.laravel.title') }}</h6>
                    <p class="text-muted mb-2">{{ t('api_documentation.examples.laravel.description') }}</p>
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

                    <h6 class="mt-4" id="exemple-flutter">{{ t('api_documentation.examples.flutter.title') }}</h6>
                    <p class="text-muted mb-2">{{ t('api_documentation.examples.flutter.description') }}</p>
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
                    <h5 class="mb-0">{{ t('api_documentation.best_practices') }}</h5>
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
                    <h5 class="mb-0">{{ t('api_documentation.support.title') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ t('api_documentation.support.description') }}</p>
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
@endpush

@push('scripts')
<script src="{{ asset('js/anchor-scroll.js') }}"></script>
@endpush