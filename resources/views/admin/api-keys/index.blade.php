@extends('admin.layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('title', t('api_keys.management'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ t('api_keys.management') }}</h1>
        <a href="{{ route('admin.api-keys.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ t('api_keys.create') }}
        </a>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.api-keys.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="project_id" class="form-label">{{ t('api_keys.project') }}</label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value="">{{ t('api_keys.all_projects') }}</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">{{ t('api_keys.status') }}</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">{{ t('api_keys.all_status') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ t('api_keys.active_plural') }}</option>
                        <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>{{ t('api_keys.revoked_plural') }}</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>{{ t('api_keys.expired_plural') }}</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>{{ t('api_keys.used_plural') }}</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> {{ t('common.filter') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des clés API -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ t('api_keys.name') }}</th>
                            <th>{{ t('api_keys.project') }}</th>
                            <th>{{ t('api_keys.key') }}</th>
                            <th>{{ t('api_keys.status') }}</th>
                            <th>{{ t('api_keys.last_used') }}</th>
                            <th>{{ t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apiKeys as $apiKey)
                        <tr>
                            <td>{{ $apiKey->name }}</td>
                            <td>{{ $apiKey->project->name }}</td>
                            <td>
                                <code>{{ Str::limit($apiKey->key, 20) }}</code>
                            </td>
                            <td>
                                @if($apiKey->is_active)
                                <span class="badge badge-success">{{ t('api_keys.active') }}</span>
                                @elseif($apiKey->is_revoked)
                                <span class="badge badge-danger">{{ t('api_keys.revoked') }}</span>
                                @elseif($apiKey->is_expired)
                                <span class="badge badge-warning">{{ t('api_keys.expired') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($apiKey->last_used_at)
                                {{ $apiKey->last_used_at->diffForHumans() }}
                                @else
                                {{ t('api_keys.never') }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.api-keys.show', $apiKey) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($apiKey->is_active)
                                <form action="{{ route('admin.api-keys.revoke', $apiKey) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('{{ t('api_keys.confirm_revoke') }}')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @elseif($apiKey->is_revoked)
                                <form action="{{ route('admin.api-keys.reactivate', $apiKey) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('{{ t('api_keys.confirm_reactivate') }}')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('admin.api-keys.destroy', $apiKey) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ t('api_keys.confirm_delete') }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ t('api_keys.no_keys') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $apiKeys->links() }}
        </div>
    </div>
</div>
@endsection