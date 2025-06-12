@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Détails du compte') }}</span>
                    <div>
                        @if(auth()->user()->can('update', $tenant))
                            <a href="{{ route('tenant.edit', $tenant->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> {{ __('Modifier') }}
                            </a>
                        @endif
                        @if(auth()->user()->can('delete', $tenant))
                            <form action="{{ route('tenant.destroy', $tenant->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Êtes-vous sûr de vouloir supprimer ce compte ?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> {{ __('Supprimer') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Informations générales') }}</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">{{ __('Nom') }}</th>
                                    <td>{{ $tenant->name }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <td>{{ $tenant->description ?: __('Non spécifiée') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Statut') }}</th>
                                    <td>
                                        <span class="badge {{ $tenant->status === 'active' ? 'bg-success' : ($tenant->status === 'suspended' ? 'bg-danger' : 'bg-warning') }}">
                                            {{ $tenant->status === 'active' ? __('Actif') : ($tenant->status === 'suspended' ? __('Suspendu') : __('Inactif')) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>{{ __('Date de création') }}</th>
                                    <td>{{ $tenant->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('Dernière mise à jour') }}</th>
                                    <td>{{ $tenant->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Informations de licence') }}</h5>
                            @if($tenant->licence)
                                <table class="table table-sm">
                                    <tr>
                                        <th style="width: 30%">{{ __('Clé de licence') }}</th>
                                        <td>{{ $tenant->licence->serial_key }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ t('serial_keys.licence_type') }}</th>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $tenant->licence->licence_type === 'single' ? t('serial_keys.single_account_licence') : t('serial_keys.multi_account_licence') }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($tenant->licence->licence_type === 'multi')
                                        <tr>
                                            <th>{{ __('Comptes utilisés') }}</th>
                                            <td>{{ $tenant->licence->activeTenantsCount() }} / {{ $tenant->licence->max_accounts }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>{{ __('Date d\'expiration') }}</th>
                                        <td>
                                            @if($tenant->licence->expiry_date)
                                                {{ $tenant->licence->expiry_date->format('d/m/Y') }}
                                                @if($tenant->licence->expiry_date->isPast())
                                                    <span class="badge bg-danger">{{ __('Expirée') }}</span>
                                                @elseif($tenant->licence->expiry_date->diffInDays(now()) < 30)
                                                    <span class="badge bg-warning">{{ __('Expire bientôt') }}</span>
                                                @endif
                                            @else
                                                {{ __('Pas de date d\'expiration') }}
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    {{ __('Aucune licence associée à ce compte.') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">{{ __('Abonnement') }}</h5>
                            @if($tenant->hasActiveSubscription())
                                <table class="table table-sm">
                                    <tr>
                                        <th style="width: 30%">{{ __('ID d\'abonnement') }}</th>
                                        <td>{{ $tenant->subscription_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Statut') }}</th>
                                        <td>
                                            <span class="badge bg-success">{{ __('Actif') }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Date de fin') }}</th>
                                        <td>
                                            @if($tenant->subscription_ends_at)
                                                {{ $tenant->subscription_ends_at->format('d/m/Y') }}
                                                ({{ $tenant->subscription_ends_at->diffForHumans() }})
                                            @else
                                                {{ __('Non spécifiée') }}
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                
                                <div class="mt-3">
                                    <a href="{{ route('billing.invoices') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-file-invoice"></i> {{ __('Voir les factures') }}
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <p>{{ __('Aucun abonnement actif pour ce compte.') }}</p>
                                    <a href="{{ route('billing.plans') }}" class="btn btn-primary">
                                        {{ __('Voir les plans d\'abonnement') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">{{ __('Utilisateurs associés') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Nom') }}</th>
                                            <th>{{ __('Email') }}</th>
                                            <th>{{ __('Rôle') }}</th>
                                            <th>{{ __('Dernier accès') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tenant->users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    <span class="badge {{ $user->pivot->role === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                                                        {{ $user->pivot->role === 'admin' ? __('Administrateur') : __('Utilisateur') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $user->pivot->last_accessed_at ? $user->pivot->last_accessed_at->diffForHumans() : __('Jamais') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">{{ __('Aucun utilisateur associé à ce compte.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if(auth()->user()->can('manageUsers', $tenant))
                                <div class="mt-3">
                                    <a href="{{ route('tenant.users.manage', $tenant->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-users-cog"></i> {{ __('Gérer les utilisateurs') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
