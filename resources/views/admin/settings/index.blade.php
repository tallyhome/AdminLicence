@extends('admin.layouts.app')

@section('title', t('settings.title'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('settings.title') }}</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
        </a>
    </div>


    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('settings.general.title') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="site_name" class="form-label">{{ t('settings.general.site_name') }}</label>
                            <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                   id="site_name" name="site_name" 
                                   value="{{ old('site_name', $settings['site_name']) }}" required>
                            @error('site_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="site_description" class="form-label">{{ t('settings.general.site_description') }}</label>
                            <textarea class="form-control @error('site_description') is-invalid @enderror" 
                                      id="site_description" name="site_description" 
                                      rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                            @error('site_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contact_email" class="form-label">{{ t('settings.general.contact_email') }}</label>
                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                   id="contact_email" name="contact_email" 
                                   value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" required>
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contact_name" class="form-label">{{ t('settings.general.contact_name') }}</label>
                            <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                                   id="contact_name" name="contact_name" 
                                   value="{{ old('contact_name', $settings['contact_name'] ?? '') }}" required>
                            @error('contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ t('common.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">


        <!-- Changer le mot de passe -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('settings.password.title') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update-password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">{{ t('settings.password.current_password') }}</label>
                            <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ t('settings.password.new_password') }}</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">{{ t('settings.password.confirm_password') }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> {{ t('settings.password.update_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Favicon -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('settings.favicon.title') }}</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p>{{ t('settings.favicon.current') }} :</p>
                        <img src="{{ asset('favicon.ico') }}" alt="Favicon" class="img-thumbnail" style="max-width: 64px;">
                    </div>

                    <form action="{{ route('admin.settings.update-favicon') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="favicon" class="form-label">{{ t('settings.favicon.new') }}</label>
                            <input type="file" id="favicon" name="favicon" class="form-control @error('favicon') is-invalid @enderror" required>
                            <div class="form-text">{{ t('settings.favicon.accepted_formats') }}</div>
                            @error('favicon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> {{ t('settings.favicon.update_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Thème -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('settings.theme.title') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.toggle-dark-mode') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="dark_mode" name="dark_mode" {{ $darkModeEnabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="dark_mode">{{ t('settings.theme.dark_mode') }}</label>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-palette"></i> {{ t('settings.theme.apply_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Gestion de licence -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestion de licence</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Gérez la licence d'installation de votre application AdminLicence et configurez les paramètres de vérification périodique.
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('admin.settings.license') }}" class="btn btn-primary">
                            <i class="fas fa-key"></i> Gérer la licence
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Authentification à deux facteurs -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('settings.two_factor.title') }}</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        {{ t('settings.two_factor.description') }}
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            @if(auth()->guard('admin')->user()->two_factor_enabled)
                                <span class="badge bg-success">{{ t('settings.two_factor.status.enabled') }}</span>
                            @else
                                <span class="badge bg-warning">{{ t('settings.two_factor.status.disabled') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('admin.settings.two-factor') }}" class="btn btn-primary me-2">
                            <i class="fas fa-shield-alt"></i> {{ t('settings.two_factor.configure') }}
                        </a>
                        <a href="{{ route('admin.settings.test-google2fa') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-vial"></i> {{ t('settings.two_factor.test') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Outils d'optimisation -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Outils d'optimisation</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Optimisez les performances de votre application avec des outils pour nettoyer les logs, optimiser les images et gérer les assets.
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('admin.settings.optimization') }}" class="btn btn-primary">
                            <i class="fas fa-tools"></i> Accéder aux outils d'optimisation
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Diagnostic API -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Diagnostic API</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Testez et diagnostiquez les fonctionnalités API de votre application : validation de clés, connexion à la base de données, permissions et logs.
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('admin.settings.api-diagnostic') }}" class="btn btn-primary me-2">
                            <i class="fas fa-stethoscope"></i> Interface de diagnostic
                        </a>
                        <a href="{{ url('/api-diagnostic.php') }}" target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-external-link-alt"></i> Outil autonome
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection