@extends('admin.layouts.app')

@section('title', 'Diagnostic API')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Diagnostic API</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.settings') }}">Paramètres</a></li>
        <li class="breadcrumb-item active">Diagnostic API</li>
    </ol>
    
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif
    
    <div class="row">
        <div class="col-xl-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-tools me-1"></i>
                        Outil de diagnostic API
                    </div>
                    <a href="{{ $apiDiagnosticUrl }}" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Ouvrir dans une nouvelle fenêtre
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Informations d'accès</h5>
                        <p>L'outil de diagnostic API est accessible à l'URL suivante : <code>{{ $apiDiagnosticUrl }}</code></p>
                        <p><strong>Identifiants par défaut :</strong> <code>admin</code> / <code>AdminLicence2025</code></p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Fonctionnalités disponibles</h5>
                            <ul class="list-group mb-4">
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    <div>
                                        <strong>Informations générales</strong>
                                        <p class="mb-0 text-muted small">Vue d'ensemble de l'API et de la configuration du serveur</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-key me-2 text-primary"></i>
                                    <div>
                                        <strong>Test de clé de série</strong>
                                        <p class="mb-0 text-muted small">Vérifiez la validité d'une clé de licence</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-network-wired me-2 text-primary"></i>
                                    <div>
                                        <strong>Test de connexion</strong>
                                        <p class="mb-0 text-muted small">Vérifiez la connectivité avec l'API externe</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-database me-2 text-primary"></i>
                                    <div>
                                        <strong>Test de base de données</strong>
                                        <p class="mb-0 text-muted small">Vérifiez la connexion à la base de données</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-lock me-2 text-primary"></i>
                                    <div>
                                        <strong>Vérification des permissions</strong>
                                        <p class="mb-0 text-muted small">Contrôlez les permissions des fichiers critiques</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-file-alt me-2 text-primary"></i>
                                    <div>
                                        <strong>Affichage des logs</strong>
                                        <p class="mb-0 text-muted small">Consultez les dernières entrées de log</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="embed-responsive" style="height: 500px; border: 1px solid #ddd; border-radius: 4px;">
                                <iframe src="{{ $apiDiagnosticUrl }}" style="width: 100%; height: 100%; border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Test de clé de série -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-key me-1"></i>
                    Test de clé de série
                </div>
                <div class="card-body">
                    <form id="serialKeyForm">
                        <div class="mb-3">
                            <label for="serialKey" class="form-label">Clé de série</label>
                            <select class="form-select" id="serialKey" name="serial_key">
                                <option value="">-- Sélectionnez une clé --</option>
                                @foreach($serialKeys as $key)
                                <option value="{{ $key->serial_key }}">{{ $key->serial_key }} ({{ $key->project->name ?? 'Aucun projet' }})</option>
                                @endforeach
                                <option value="custom">Saisir une clé personnalisée</option>
                            </select>
                        </div>
                        <div class="mb-3" id="customKeyField" style="display: none;">
                            <label for="customKey" class="form-label">Clé personnalisée</label>
                            <input type="text" class="form-control" id="customKey" placeholder="XXXX-XXXX-XXXX-XXXX">
                        </div>
                        <div class="mb-3">
                            <label for="domain" class="form-label">Domaine (optionnel)</label>
                            <input type="text" class="form-control" id="domain" name="domain" placeholder="exemple.com">
                        </div>
                        <div class="mb-3">
                            <label for="ipAddress" class="form-label">Adresse IP (optionnel)</label>
                            <input type="text" class="form-control" id="ipAddress" name="ip_address" placeholder="192.168.1.1">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle me-1"></i> Tester la clé
                        </button>
                    </form>
                    
                    <div id="serialKeyResult" class="mt-4" style="display: none;">
                        <h5>Résultat</h5>
                        <div id="serialKeyResultContent" class="p-3 border rounded"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations sur le serveur -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-server me-1"></i>
                    Informations sur le serveur
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Version PHP</th>
                                <td>{{ $serverInfo['php_version'] }}</td>
                            </tr>
                            <tr>
                                <th>Version Laravel</th>
                                <td>{{ $serverInfo['laravel_version'] }}</td>
                            </tr>
                            <tr>
                                <th>Serveur Web</th>
                                <td>{{ $serverInfo['server_software'] }}</td>
                            </tr>
                            <tr>
                                <th>Système d'exploitation</th>
                                <td>{{ $serverInfo['os'] }}</td>
                            </tr>
                            <tr>
                                <th>Base de données</th>
                                <td>{{ $serverInfo['database'] }}</td>
                            </tr>
                            <tr>
                                <th>Fuseau horaire</th>
                                <td>{{ $serverInfo['timezone'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h5 class="mt-4">Extensions PHP</h5>
                    <div class="row">
                        @foreach($serverInfo['extensions'] as $extension => $loaded)
                        <div class="col-md-4 mb-2">
                            <span class="badge {{ $loaded ? 'bg-success' : 'bg-danger' }}">
                                <i class="fas {{ $loaded ? 'fa-check' : 'fa-times' }} me-1"></i>
                                {{ $extension }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-4">
                        <button id="testApiConnectionBtn" class="btn btn-outline-primary me-2">
                            <i class="fas fa-network-wired me-1"></i> Tester la connexion API
                        </button>
                        <button id="testDatabaseBtn" class="btn btn-outline-primary me-2">
                            <i class="fas fa-database me-1"></i> Tester la base de données
                        </button>
                        <button id="checkPermissionsBtn" class="btn btn-outline-primary">
                            <i class="fas fa-lock me-1"></i> Vérifier les permissions
                        </button>
                    </div>
                    
                    <div id="testResult" class="mt-4" style="display: none;">
                        <h5 id="testResultTitle">Résultat</h5>
                        <div id="testResultContent" class="p-3 border rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Statistiques de la base de données -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Statistiques de la base de données
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Clés de série</div>
                                            <div class="text-lg fw-bold">{{ $dbStats['serial_keys'] }}</div>
                                        </div>
                                        <i class="fas fa-key fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Projets</div>
                                            <div class="text-lg fw-bold">{{ $dbStats['projects'] }}</div>
                                        </div>
                                        <i class="fas fa-project-diagram fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Administrateurs</div>
                                            <div class="text-lg fw-bold">{{ $dbStats['admins'] }}</div>
                                        </div>
                                        <i class="fas fa-users-cog fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="me-3">
                                            <div class="text-white-75 small">Clés actives</div>
                                            <div class="text-lg fw-bold">{{ $dbStats['active_keys'] }}</div>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Derniers logs -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file-alt me-1"></i>
                        Derniers logs
                    </div>
                    <button id="refreshLogsBtn" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-sync-alt me-1"></i> Rafraîchir
                    </button>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                        <div id="logsContent">
                            @if(count($logEntries) > 0)
                                @foreach($logEntries as $entry)
                                <div class="log-entry mb-2">
                                    <span class="text-muted small">[{{ $entry['timestamp'] }}]</span>
                                    <pre class="mb-0 mt-1" style="white-space: pre-wrap; font-size: 0.8rem;">{{ $entry['content'] }}</pre>
                                </div>
                                @endforeach
                            @else
                                <p class="text-muted">Aucune entrée de log disponible</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion du champ de clé personnalisée
        const serialKeySelect = document.getElementById('serialKey');
        const customKeyField = document.getElementById('customKeyField');
        const customKeyInput = document.getElementById('customKey');
        
        serialKeySelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customKeyField.style.display = 'block';
            } else {
                customKeyField.style.display = 'none';
            }
        });
        
        // Test de clé de série
        const serialKeyForm = document.getElementById('serialKeyForm');
        const serialKeyResult = document.getElementById('serialKeyResult');
        const serialKeyResultContent = document.getElementById('serialKeyResultContent');
        
        serialKeyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const serialKey = serialKeySelect.value === 'custom' ? customKeyInput.value : serialKeySelect.value;
            const domain = document.getElementById('domain').value;
            const ipAddress = document.getElementById('ipAddress').value;
            
            if (!serialKey) {
                alert('Veuillez sélectionner ou saisir une clé de série');
                return;
            }
            
            // Afficher un indicateur de chargement
            serialKeyResult.style.display = 'block';
            serialKeyResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Vérification en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('{{ route('admin.settings.api-diagnostic.test-serial-key') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    serial_key: serialKey,
                    domain: domain,
                    ip_address: ipAddress
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const result = data.result;
                    let statusClass = result.valid ? 'success' : 'danger';
                    let statusText = result.valid ? 'Valide' : 'Invalide';
                    
                    let html = `
                        <div class="alert alert-${statusClass}">
                            <strong>Statut : ${statusText}</strong><br>
                            Message : ${result.message}
                        </div>
                        <div class="mt-3">
                            <h6>Détails</h6>
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Projet</th>
                                        <td>${result.project || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Date d'expiration</th>
                                        <td>${result.expires_at || 'Aucune'}</td>
                                    </tr>
                                    <tr>
                                        <th>Statut</th>
                                        <td>${result.status || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Token</th>
                                        <td><code>${result.token || 'N/A'}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    serialKeyResultContent.innerHTML = html;
                } else {
                    serialKeyResultContent.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Erreur</strong><br>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                serialKeyResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Test de connexion API
        const testApiConnectionBtn = document.getElementById('testApiConnectionBtn');
        const testResult = document.getElementById('testResult');
        const testResultTitle = document.getElementById('testResultTitle');
        const testResultContent = document.getElementById('testResultContent');
        
        testApiConnectionBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            testResult.style.display = 'block';
            testResultTitle.textContent = 'Test de connexion API';
            testResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Test en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('{{ route('admin.settings.api-diagnostic.test-api-connection') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                let statusClass = data.success ? 'success' : 'danger';
                let statusText = data.success ? 'Succès' : 'Échec';
                
                let html = `
                    <div class="alert alert-${statusClass}">
                        <strong>Statut : ${statusText}</strong><br>
                        Code HTTP : ${data.status_code || 'N/A'}
                    </div>
                `;
                
                if (data.response) {
                    html += `
                        <div class="mt-3">
                            <h6>Réponse</h6>
                            <pre class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(data.response, null, 2)}</pre>
                        </div>
                    `;
                }
                
                testResultContent.innerHTML = html;
            })
            .catch(error => {
                testResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Test de base de données
        const testDatabaseBtn = document.getElementById('testDatabaseBtn');
        
        testDatabaseBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            testResult.style.display = 'block';
            testResultTitle.textContent = 'Test de connexion à la base de données';
            testResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Test en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('{{ route('admin.settings.api-diagnostic.test-database') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                let statusClass = data.success ? 'success' : 'danger';
                let statusText = data.success ? 'Succès' : 'Échec';
                
                let html = `
                    <div class="alert alert-${statusClass}">
                        <strong>Statut : ${statusText}</strong><br>
                        ${data.message}
                    </div>
                `;
                
                if (data.success) {
                    html += `
                        <div class="mt-3">
                            <h6>Informations</h6>
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Driver</th>
                                        <td>${data.driver}</td>
                                    </tr>
                                    <tr>
                                        <th>Base de données</th>
                                        <td>${data.database}</td>
                                    </tr>
                                    <tr>
                                        <th>Version</th>
                                        <td>${data.version}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                testResultContent.innerHTML = html;
            })
            .catch(error => {
                testResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Vérification des permissions
        const checkPermissionsBtn = document.getElementById('checkPermissionsBtn');
        
        checkPermissionsBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            testResult.style.display = 'block';
            testResultTitle.textContent = 'Vérification des permissions';
            testResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Vérification en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('{{ route('admin.settings.api-diagnostic.check-permissions') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                let statusClass = data.success ? 'success' : 'warning';
                let statusText = data.success ? 'OK' : 'Attention';
                
                let html = `
                    <div class="alert alert-${statusClass}">
                        <strong>Statut : ${statusText}</strong><br>
                        ${data.message}
                    </div>
                `;
                
                if (data.permissions) {
                    html += `
                        <div class="mt-3">
                            <h6>Permissions des fichiers</h6>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fichier/Dossier</th>
                                        <th>Existe</th>
                                        <th>Permissions</th>
                                        <th>Accès en écriture</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.permissions.forEach(item => {
                        const existsClass = item.exists ? 'success' : 'danger';
                        const writableClass = item.writable ? 'success' : (item.should_be_writable ? 'danger' : 'warning');
                        
                        html += `
                            <tr>
                                <td>${item.name}</td>
                                <td><span class="badge bg-${existsClass}">${item.exists ? 'Oui' : 'Non'}</span></td>
                                <td>${item.permissions}</td>
                                <td><span class="badge bg-${writableClass}">${item.writable ? 'Oui' : 'Non'}</span></td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                testResultContent.innerHTML = html;
            })
            .catch(error => {
                testResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Rafraîchir les logs
        const refreshLogsBtn = document.getElementById('refreshLogsBtn');
        const logsContent = document.getElementById('logsContent');
        
        refreshLogsBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            logsContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Chargement des logs...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('{{ route('admin.settings.api-diagnostic.get-logs') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.logs.length > 0) {
                    let html = '';
                    
                    data.logs.forEach(entry => {
                        html += `
                            <div class="log-entry mb-2">
                                <span class="text-muted small">[${entry.timestamp}]</span>
                                <pre class="mb-0 mt-1" style="white-space: pre-wrap; font-size: 0.8rem;">${entry.content}</pre>
                            </div>
                        `;
                    });
                    
                    logsContent.innerHTML = html;
                } else {
                    logsContent.innerHTML = '<p class="text-muted">Aucune entrée de log disponible</p>';
                }
            })
            .catch(error => {
                logsContent.innerHTML = '<p class="text-danger">Erreur lors du chargement des logs</p>';
                console.error('Error:', error);
            });
        });
    });
</script>
@endsection
