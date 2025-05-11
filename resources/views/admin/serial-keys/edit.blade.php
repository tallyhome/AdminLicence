@extends('admin.layouts.app')

@section('title', t('serial_keys.edit_key'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('serial_keys.edit_key') }}</h1>
        <a href="{{ route('admin.serial-keys.show', $serialKey) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ t('serial_keys.information') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.serial-keys.update', $serialKey) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Projet (non modifiable) -->
                        <div class="mb-3">
                            <label class="form-label">{{ t('serial_keys.project') }}</label>
                            <div class="form-control-plaintext">
                                {{ $serialKey->project->name }}
                            </div>
                            <input type="hidden" name="project_id" value="{{ $serialKey->project_id }}">
                        </div>
                        
                        <!-- Statut -->
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ t('serial_keys.status') }}</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $serialKey->status) === 'active' ? 'selected' : '' }}>{{ t('serial_keys.status_active') }}</option>
                                <option value="suspended" {{ old('status', $serialKey->status) === 'suspended' ? 'selected' : '' }}>{{ t('serial_keys.status_suspended') }}</option>
                                <option value="revoked" {{ old('status', $serialKey->status) === 'revoked' ? 'selected' : '' }}>{{ t('serial_keys.status_revoked') }}</option>
                                <option value="expired" {{ old('status', $serialKey->status) === 'expired' ? 'selected' : '' }}>{{ t('serial_keys.status_expired') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Domaine -->
                        <div class="mb-3">
                            <label for="domain" class="form-label">{{ t('serial_keys.domain') }}</label>
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" id="domain" name="domain" value="{{ old('domain', $serialKey->domain) }}">
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Adresse IP -->
                        <div class="mb-3">
                            <label for="ip_address" class="form-label">{{ t('serial_keys.ip_address') }}</label>
                            <input type="text" class="form-control @error('ip_address') is-invalid @enderror" id="ip_address" name="ip_address" value="{{ old('ip_address', $serialKey->ip_address) }}">
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Date d'expiration -->
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">{{ t('serial_keys.expiration_date') }}</label>
                            <input type="date" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" name="expires_at" value="{{ old('expires_at', $serialKey->expires_at ? $serialKey->expires_at->format('Y-m-d') : '') }}">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ t('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection