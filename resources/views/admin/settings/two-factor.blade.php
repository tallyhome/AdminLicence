@extends('admin.layouts.app')

@section('title', t('settings.two_factor.title'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('settings.two_factor.title') }}</h1>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">{{ t('settings.two_factor.configuration.title') }}</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        {{ t('settings.two_factor.configuration.description') }}
                    </p>

                    @if ($admin->two_factor_enabled)
                        <div class="alert alert-info">
                            <i class="fas fa-shield-alt"></i> {{ t('settings.two_factor.status.enabled_message') }}
                        </div>

                        <form action="{{ route('admin.settings.two-factor.disable') }}" method="POST" class="mt-3">
                            @csrf
                            <div class="mb-3">
                                <label for="code" class="form-label">{{ t('settings.two_factor.auth_code') }}</label>
                                <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" 
                                       required maxlength="6" placeholder="123456">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times-circle"></i> {{ t('settings.two_factor.disable') }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> {{ t('settings.two_factor.status.disabled_message') }}
                        </div>

                        <div class="mt-3">
                            <p class="mb-2">{{ t('settings.two_factor.setup.step1') }}</p>
                            <div class="text-center p-3 bg-light rounded mb-3">
                                <img src="{{ $qrCodeUrl }}" 
                                     alt="QR Code" class="img-fluid">
                            </div>
                            
                            <p class="mb-2">{{ t('settings.two_factor.setup.step2') }}</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="{{ $secret }}" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copySecret()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            
                            <p class="mb-2">{{ t('settings.two_factor.setup.step3') }}</p>
                            <form action="{{ route('admin.settings.two-factor.enable') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="code" class="form-label">{{ t('settings.two_factor.auth_code') }}</label>
                                    <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" 
                                           required maxlength="6" placeholder="123456">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-shield-alt"></i> {{ t('settings.two_factor.enable') }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if ($admin->two_factor_enabled)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Codes de récupération</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">
                            Les codes de récupération vous permettent de vous connecter à votre compte si vous n'avez pas accès 
                            à votre application d'authentification. <strong>Conservez-les dans un endroit sûr</strong>, car ils ne seront 
                            affichés qu'une seule fois.
                        </p>

                        @if (session('recoveryCodes'))
                            <div class="alert alert-warning">
                                <p><strong>Conservez ces codes de récupération dans un endroit sûr :</strong></p>
                                <ul class="list-group mb-3">
                                    @foreach (session('recoveryCodes') as $code)
                                        <li class="list-group-item">{{ $code }}</li>
                                    @endforeach
                                </ul>
                                <p class="mb-0"><small>Chaque code ne peut être utilisé qu'une seule fois.</small></p>
                            </div>
                        @endif

                        <form action="{{ route('admin.settings.two-factor.regenerate-recovery-codes') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-sync"></i> Régénérer les codes de récupération
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if (!$admin->two_factor_enabled)
<script>
function copySecret() {
    const secretInput = document.querySelector('input[value="{{ $secret }}"]');
    secretInput.select();
    document.execCommand('copy');
    alert('{{ t("settings.two_factor.secret_copied") }}');
}
</script>
@endif
@endsection