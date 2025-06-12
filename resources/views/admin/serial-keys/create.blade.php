@extends('admin.layouts.app')

@section('title', t('serial_keys.create_key'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('serial_keys.create_key') }}</h1>
        <a href="{{ route('admin.serial-keys.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ t('serial_keys.create_form') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.serial-keys.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Projet -->
                        <div class="mb-3">
                            <label for="project_id" class="form-label">{{ t('serial_keys.project') }}</label>
                            <select id="project_id" name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                <option value="">{{ t('serial_keys.select_project') }}</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Quantité -->
                        <div class="mb-3">
                            <label for="quantity" class="form-label">{{ t('serial_keys.quantity') }}</label>
                            <input type="number" id="quantity" name="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                   value="{{ old('quantity', 1) }}" min="1" max="100000" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Domaine -->
                        <div class="mb-3">
                            <label for="domain" class="form-label">{{ t('serial_keys.domain_optional') }}</label>
                            <input type="text" id="domain" name="domain" class="form-control @error('domain') is-invalid @enderror" 
                                   value="{{ old('domain') }}" placeholder="exemple.com">
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Adresse IP -->
                        <div class="mb-3">
                            <label for="ip_address" class="form-label">{{ t('serial_keys.ip_address_optional') }}</label>
                            <input type="text" id="ip_address" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror" 
                                   value="{{ old('ip_address') }}" placeholder="192.168.1.1">
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Date d'expiration -->
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">{{ t('serial_keys.expiration_date_optional') }}</label>
                            <input type="date" id="expires_at" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" 
                                   value="{{ old('expires_at') }}">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <!-- Type de licence -->
                        <div class="mb-3">
                            <label for="licence_type" class="form-label">{{ t('serial_keys.licence_type') }}</label>
                            <select id="licence_type" name="licence_type" class="form-select @error('licence_type') is-invalid @enderror" required>
                                @foreach($licenceTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('licence_type', 'single') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('licence_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Nombre maximum de comptes (pour licence multi) -->
                        <div class="mb-3" id="max_accounts_field" style="{{ old('licence_type', 'single') === 'multi' ? '' : 'display: none;' }}">
                            <label for="max_accounts" class="form-label">{{ t('serial_keys.max_accounts') }}</label>
                            <input type="number" class="form-control @error('max_accounts') is-invalid @enderror" id="max_accounts" name="max_accounts" value="{{ old('max_accounts') }}" min="1" max="1000">
                            @error('max_accounts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ t('serial_keys.create_keys') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const licenceTypeSelect = document.getElementById('licence_type');
    const maxAccountsField = document.getElementById('max_accounts_field');
    const maxAccountsInput = document.getElementById('max_accounts');
    
    function toggleMaxAccountsField() {
        if (licenceTypeSelect.value === 'multi') {
            maxAccountsField.style.display = '';
            maxAccountsInput.required = true;
        } else {
            maxAccountsField.style.display = 'none';
            maxAccountsInput.required = false;
            maxAccountsInput.value = '';
        }
    }
    
    licenceTypeSelect.addEventListener('change', toggleMaxAccountsField);
    toggleMaxAccountsField(); // Initialiser l'état au chargement
});
</script>
@endsection