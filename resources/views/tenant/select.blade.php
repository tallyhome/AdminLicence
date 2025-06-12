@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Sélection du compte') }}</div>

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

                    <h4 class="mb-4">{{ __('Sélectionnez un compte pour continuer') }}</h4>

                    <div class="row">
                        @forelse ($tenants as $tenant)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 {{ $tenant->status === 'suspended' ? 'border-danger' : ($tenant->status === 'inactive' ? 'border-warning' : 'border-primary') }}">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $tenant->name }}</h5>
                                        @if ($tenant->description)
                                            <p class="card-text text-muted">{{ $tenant->description }}</p>
                                        @endif
                                        
                                        <div class="mb-2">
                                            <span class="badge {{ $tenant->status === 'active' ? 'bg-success' : ($tenant->status === 'suspended' ? 'bg-danger' : 'bg-warning') }}">
                                                {{ $tenant->status === 'active' ? 'Actif' : ($tenant->status === 'suspended' ? 'Suspendu' : 'Inactif') }}
                                            </span>
                                            
                                            @if ($tenant->licence)
                                                <span class="badge bg-info">
                                                    {{ $tenant->licence->licence_type === 'single' ? t('serial_keys.single_account_licence') : t('serial_keys.multi_account_licence') }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Dernier accès: {{ $tenant->pivot->last_accessed_at ? $tenant->pivot->last_accessed_at->diffForHumans() : 'Jamais' }}</small>
                                            
                                            @if ($tenant->status === 'active')
                                                <a href="{{ route('tenant.switch', $tenant->id) }}" class="btn btn-primary">Sélectionner</a>
                                            @elseif ($tenant->status === 'suspended')
                                                <button class="btn btn-danger" disabled>Compte suspendu</button>
                                            @else
                                                <button class="btn btn-warning" disabled>Compte inactif</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <p>{{ __('Vous n\'avez accès à aucun compte pour le moment.') }}</p>
                                    
                                    @if (auth()->user()->can('create', App\Models\Tenant::class))
                                        <p class="mb-0">
                                            <a href="{{ route('tenant.create') }}" class="btn btn-primary">
                                                {{ __('Créer un nouveau compte') }}
                                            </a>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if (auth()->user()->can('create', App\Models\Tenant::class))
                        <div class="mt-4">
                            <a href="{{ route('tenant.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> {{ __('Créer un nouveau compte') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
