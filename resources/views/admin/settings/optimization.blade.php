@extends('admin.layouts.app')

@section('title', t('optimization.title'))

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">{{ t('optimization.title') }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ t('common.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.settings') }}">{{ t('common.settings') }}</a></li>
        <li class="breadcrumb-item active">{{ t('optimization.title') }}</li>
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
    
    @if(session('output'))
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-terminal me-1"></i>
            {{ t('optimization.operation_result') }}
        </div>
        <div class="card-body">
            <pre class="bg-dark text-light p-3" style="max-height: 300px; overflow-y: auto;">{{ session('output') }}</pre>
        </div>
    </div>
    @endif
    
    @if(session('example'))
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-code me-1"></i>
            {{ t('optimization.example_code') }}
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p>{{ t('optimization.copy_code') }}</p>
                <code>{{ session('example') }}</code>
            </div>
        </div>
    </div>
    @endif
    
    <div class="row">
        <!-- Nettoyage des logs -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-trash me-1"></i>
                    {{ t('optimization.logs_cleaning') }}
                </div>
                <div class="card-body">
                    <p>{{ t('optimization.current_logs_size') }} : <strong>{{ $logsSize }}</strong></p>
                    <p>{{ t('optimization.logs_cleaning_description') }}</p>
                    
                    <ul class="nav nav-tabs mb-3" id="logsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-logs-tab" data-bs-toggle="tab" data-bs-target="#all-logs" type="button" role="tab" aria-controls="all-logs" aria-selected="true">
                                {{ t('optimization.all_logs') }}
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="install-logs-tab" data-bs-toggle="tab" data-bs-target="#install-logs" type="button" role="tab" aria-controls="install-logs" aria-selected="false">
                                {{ t('optimization.installation_logs') }} <span class="badge bg-secondary">{{ $installLogsSize }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="laravel-logs-tab" data-bs-toggle="tab" data-bs-target="#laravel-logs" type="button" role="tab" aria-controls="laravel-logs" aria-selected="false">
                                {{ t('optimization.laravel_logs') }} <span class="badge bg-secondary">{{ $laravelLogsSize }}</span>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="logsTabsContent">
                        <!-- Tous les logs -->
                        <div class="tab-pane fade show active" id="all-logs" role="tabpanel" aria-labelledby="all-logs-tab">
                            <div class="d-flex mb-3">
                                <form action="{{ route('admin.settings.optimization.clean-logs') }}" method="POST" class="me-2">
                                    @csrf
                                    <input type="hidden" name="log_type" value="all">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-broom me-1"></i> {{ t('optimization.clean_all_logs') }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.settings.optimization.clean-logs') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="log_type" value="all">
                                    <input type="hidden" name="delete_all" value="1">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('{{ t("optimization.delete_all_logs_confirm") }}');">
                                        <i class="fas fa-trash-alt me-1"></i> {{ t('optimization.delete_all_logs') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Logs d'installation -->
                        <div class="tab-pane fade" id="install-logs" role="tabpanel" aria-labelledby="install-logs-tab">
                            <div class="d-flex mb-3">
                                <form action="{{ route('admin.settings.optimization.clean-logs') }}" method="POST" class="me-2">
                                    @csrf
                                    <input type="hidden" name="log_type" value="install">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-broom me-1"></i> {{ t('optimization.clean_installation_logs') }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.settings.optimization.clean-logs') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="log_type" value="install">
                                    <input type="hidden" name="delete_all" value="1">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('{{ t("optimization.delete_installation_logs_confirm") }}');">
                                        <i class="fas fa-trash-alt me-1"></i> {{ t('optimization.delete_installation_logs') }}
                                    </button>
                                </form>
                            </div>
                            
                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nom du fichier</th>
                                            <th>Taille</th>
                                            <th>Date de modification</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($installLogFiles as $log)
                                        <tr>
                                            <td>{{ $log['name'] }}</td>
                                            <td>{{ $log['size'] }}</td>
                                            <td>{{ $log['date'] }}</td>
                                            <td>
                                                <a href="{{ route('admin.settings.optimization.view-log', ['path' => $log['path']]) }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">{{ t('optimization.no_installation_logs_found') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Logs Laravel -->
                        <div class="tab-pane fade" id="laravel-logs" role="tabpanel" aria-labelledby="laravel-logs-tab">
                            <div class="d-flex mb-3">
                                <form action="{{ route('admin.settings.optimization.clean-logs') }}" method="POST" class="me-2">
                                    @csrf
                                    <input type="hidden" name="log_type" value="laravel">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-broom me-1"></i> {{ t('optimization.clean_laravel_logs') }}
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.settings.optimization.clean-logs') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="log_type" value="laravel">
                                    <input type="hidden" name="delete_all" value="1">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('{{ t("optimization.delete_laravel_logs_confirm") }}');">
                                        <i class="fas fa-trash-alt me-1"></i> {{ t('optimization.delete_laravel_logs') }}
                                    </button>
                                </form>
                            </div>
                            
                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Nom du fichier</th>
                                            <th>Taille</th>
                                            <th>Date de modification</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($laravelLogFiles as $log)
                                        <tr>
                                            <td>{{ $log['name'] }}</td>
                                            <td>{{ $log['size'] }}</td>
                                            <td>{{ $log['date'] }}</td>
                                            <td>
                                                <a href="{{ route('admin.settings.optimization.view-log', ['path' => $log['path']]) }}" target="_blank" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Voir
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center">{{ t('optimization.no_laravel_logs_found') }}</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Optimisation des images -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-images me-1"></i>
                    {{ t('optimization.image_optimization') }}
                </div>
                <div class="card-body">
                    <p>{{ t('optimization.current_images_size') }} : <strong>{{ $imagesSize }}</strong></p>
                    <p>{{ t('optimization.images_optimization_description') }}</p>
                    <form action="{{ route('admin.settings.optimization.optimize-images') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="quality" class="form-label">{{ t('optimization.quality') }}</label>
                            <input type="range" class="form-range" min="60" max="95" step="5" id="quality" name="quality" value="80">
                            <div class="d-flex justify-content-between">
                                <span>{{ t('optimization.high_compression') }}</span>
                                <span id="qualityValue">80%</span>
                                <span>{{ t('optimization.high_quality') }}</span>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="force" name="force">
                            <label class="form-check-label" for="force">{{ t('optimization.force_optimization') }}</label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-compress me-1"></i> {{ t('optimization.optimize_images') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Outil de diagnostic API -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    {{ t('optimization.api_diagnostic_tool') }}
                </div>
                <div class="card-body">
                    <p>{{ t('optimization.api_diagnostic_description') }}</p>
                    <ul>
                        <li>{{ t('optimization.api_general_info') }}</li>
                        <li>{{ t('optimization.api_serial_key_test') }}</li>
                        <li>{{ t('optimization.api_connection_test') }}</li>
                        <li>{{ t('optimization.api_database_test') }}</li>
                        <li>{{ t('optimization.api_file_permissions') }}</li>
                        <li>{{ t('optimization.api_log_entries') }}</li>
                    </ul>
                    <p><strong>{{ t('optimization.api_default_credentials') }} :</strong> admin / AdminLicence2025</p>
                    <a href="{{ url('/api-diagnostic.php') }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> {{ t('optimization.open_api_diagnostic') }}
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Versioning des assets -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-code me-1"></i>
                    {{ t('optimization.asset_versioning') }}
                </div>
                <div class="card-body">
                    <p>{{ t('optimization.asset_versioning_description') }}</p>
                    
                    <div class="mb-3">
                        <label for="assetType" class="form-label">{{ t('optimization.asset_type') }}</label>
                        <select class="form-select" id="assetType">
                            <option value="css">{{ t('optimization.css_file') }}</option>
                            <option value="js">{{ t('optimization.js_file') }}</option>
                            <option value="image">{{ t('optimization.image') }}</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="cssAssets">
                        <label for="cssPath" class="form-label">{{ t('optimization.css_file') }}</label>
                        <select class="form-select" id="cssPath">
                            @foreach($cssFiles as $file)
                            <option value="{{ $file }}">{{ $file }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3" id="jsAssets" style="display: none;">
                        <label for="jsPath" class="form-label">{{ t('optimization.js_file') }}</label>
                        <select class="form-select" id="jsPath">
                            @foreach($jsFiles as $file)
                            <option value="{{ $file }}">{{ $file }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3" id="imagePath" style="display: none;">
                        <label for="imagePathInput" class="form-label">{{ t('optimization.image_path') }}</label>
                        <input type="text" class="form-control" id="imagePathInput" placeholder="images/logo.png">
                    </div>
                    
                    <form action="{{ route('admin.settings.optimization.asset-example') }}" method="POST">
                        @csrf
                        <input type="hidden" name="asset_path" id="assetPathHidden">
                        <button type="submit" class="btn btn-primary" id="generateExampleBtn">
                            <i class="fas fa-code me-1"></i> {{ t('optimization.generate_example') }}
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <h5>{{ t('optimization.how_to_use') }}</h5>
                        <p>{{ t('optimization.blade_directives_description') }}</p>
                        <ul>
                            <li><code>@versionedCss('css/app.css')</code> - Pour les fichiers CSS</li>
                            <li><code>@versionedJs('js/app.js')</code> - Pour les fichiers JavaScript</li>
                            <li><code>@versionedImage('images/logo.png')</code> - Pour les images</li>
                        </ul>
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
        // Afficher la valeur du slider de qualité
        const qualitySlider = document.getElementById('quality');
        const qualityValue = document.getElementById('qualityValue');
        
        qualitySlider.addEventListener('input', function() {
            qualityValue.textContent = this.value + '%';
        });
        
        // Gestion du changement de type d'asset
        const assetType = document.getElementById('assetType');
        const cssAssets = document.getElementById('cssAssets');
        const jsAssets = document.getElementById('jsAssets');
        const imagePath = document.getElementById('imagePath');
        const assetPathHidden = document.getElementById('assetPathHidden');
        const cssPath = document.getElementById('cssPath');
        const jsPath = document.getElementById('jsPath');
        const imagePathInput = document.getElementById('imagePathInput');
        
        // Initialiser la valeur cachée
        assetPathHidden.value = cssPath.value;
        
        assetType.addEventListener('change', function() {
            cssAssets.style.display = 'none';
            jsAssets.style.display = 'none';
            imagePath.style.display = 'none';
            
            if (this.value === 'css') {
                cssAssets.style.display = 'block';
                assetPathHidden.value = cssPath.value;
            } else if (this.value === 'js') {
                jsAssets.style.display = 'block';
                assetPathHidden.value = jsPath.value;
            } else if (this.value === 'image') {
                imagePath.style.display = 'block';
                assetPathHidden.value = imagePathInput.value;
            }
        });
        
        // Mettre à jour la valeur cachée lors du changement de sélection
        cssPath.addEventListener('change', function() {
            if (assetType.value === 'css') {
                assetPathHidden.value = this.value;
            }
        });
        
        jsPath.addEventListener('change', function() {
            if (assetType.value === 'js') {
                assetPathHidden.value = this.value;
            }
        });
        
        imagePathInput.addEventListener('input', function() {
            if (assetType.value === 'image') {
                assetPathHidden.value = this.value;
            }
        });
    });
</script>
@endsection
