@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ isset($tenant) ? __('Modifier le compte') : __('Créer un nouveau compte') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ isset($tenant) ? route('tenant.update', $tenant->id) : route('tenant.store') }}">
                        @csrf
                        @if(isset($tenant))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nom du compte') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', isset($tenant) ? $tenant->name : '') }}" required autofocus>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">{{ __('Le nom de votre compte, visible par tous les utilisateurs.') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', isset($tenant) ? $tenant->description : '') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">{{ __('Une brève description de ce compte (optionnel).') }}</div>
                        </div>

                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                            <div class="mb-3">
                                <label for="licence_id" class="form-label">{{ __('Licence') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('licence_id') is-invalid @enderror" id="licence_id" name="licence_id" required>
                                    <option value="">{{ __('Sélectionnez une licence') }}</option>
                                    @foreach($licences as $licence)
                                        <option value="{{ $licence->id }}" {{ old('licence_id', isset($tenant) ? $tenant->licence_id : '') == $licence->id ? 'selected' : '' }}>
                                            {{ $licence->serial_key }} ({{ $licence->licence_type === 'single' ? 'Mono-compte' : 'Multi-comptes' }})
                                            @if($licence->licence_type === 'multi')
                                                - {{ $licence->activeTenantsCount() }}/{{ $licence->max_accounts }} comptes utilisés
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('licence_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">{{ __('Statut') }}</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="active" {{ old('status', isset($tenant) ? $tenant->status : 'active') == 'active' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                                    <option value="inactive" {{ old('status', isset($tenant) ? $tenant->status : '') == 'inactive' ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                                    <option value="suspended" {{ old('status', isset($tenant) ? $tenant->status : '') == 'suspended' ? 'selected' : '' }}>{{ __('Suspendu') }}</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ isset($tenant) ? route('tenant.show', $tenant->id) : route('tenant.select') }}" class="btn btn-secondary">
                                {{ __('Annuler') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ isset($tenant) ? __('Mettre à jour') : __('Créer') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
