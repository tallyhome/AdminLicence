@extends('admin.layouts.app')

@section('title', t('serial_keys.title'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>{{ t('serial_keys.title') }}</h4>
        <div>
            <a href="{{ route('admin.serial-keys.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> {{ t('serial_keys.create_key') }}
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">{{ t('serial_keys.list') }}</h5>
        </div>
        <div class="card-body border-bottom pb-2 pt-2">
            <form action="{{ route('admin.serial-keys.index') }}" method="GET" id="searchForm">
                <input type="hidden" name="per_page" value="{{ request()->input('per_page', 10) }}">
                
                <div class="row align-items-end">
                    <div class="col-md-1-5">
                        <div class="form-group" style="width: 100%; min-width: 80px;">
                            <label for="status">{{ t('serial_keys.status') }}</label>
                            <select name="status" id="status" class="form-control form-control-sm">
                                <option value="">{{ t('common.all') }}</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ t('serial_keys.status_active') }}</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>{{ t('serial_keys.status_suspended') }}</option>
                                <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>{{ t('serial_keys.status_revoked') }}</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ t('serial_keys.status_expired') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1-5">
                        <div class="form-group" style="width: 100%; min-width: 80px;">
                            <label for="project">{{ t('serial_keys.project') }}</label>
                            <select name="project_id" id="project" class="form-control form-control-sm">
                                <option value="">{{ t('common.all') }}</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group" style="width: 100%; min-width: 60px;">
                            <label for="used">{{ t('serial_keys.key_used') }}</label>
                            <select name="used" id="used" class="form-control form-control-sm">
                                <option value="">{{ t('common.all') }}</option>
                                <option value="true" {{ request('used') === 'true' ? 'selected' : '' }}>{{ t('common.used') }}</option>
                                <option value="false" {{ request('used') === 'false' ? 'selected' : '' }}>{{ t('common.unused') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="domain">{{ t('serial_keys.domain') }}</label>
                            <input type="text" name="domain" id="domain" class="form-control form-control-sm" value="{{ request('domain') }}" placeholder="exemple.com">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="ip_address">{{ t('serial_keys.ip_address') }}</label>
                            <input type="text" name="ip_address" id="ip_address" class="form-control form-control-sm" value="{{ request('ip_address') }}" placeholder="127.0.0.1">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="search">{{ t('common.search') }}</label>
                            <input type="text" name="search" id="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="{{ t('serial_keys.search_placeholder') }}">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group" style="width: 100%; min-width: 40px;">
                            <label for="per_page">{{ t('pagination.per_page', ['number' => '']) }}</label>
                            <select name="per_page" id="per_page" class="form-control form-control-sm" onchange="document.getElementById('searchForm').submit()">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                                <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex justify-content-end">
                        <div class="form-group" style="width: 100%; min-width: 50px;">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>{{ t('serial_keys.key') }}</th>
                            <th>{{ t('serial_keys.project') }}</th>
                            <th>{{ t('serial_keys.status') }}</th>
                            <th>{{ t('serial_keys.domain') }}</th>
                            <th>{{ t('serial_keys.ip_address') }}</th>
                            <th>{{ t('serial_keys.expiration') }}</th>
                            <th>{{ t('serial_keys.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serialKeys as $key)
                            <tr>
                                <td>
                                    <code>{{ $key->serial_key }}</code>
                                </td>
                                <td>{{ $key->project->name }}</td>
                                <td>
                                    @if($key->status === 'active')
                                        <span class="badge bg-success">{{ t('serial_keys.status_active') }}</span>
                                    @elseif($key->status === 'suspended')
                                        <span class="badge bg-warning">{{ t('serial_keys.status_suspended') }}</span>
                                    @elseif($key->status === 'revoked')
                                        <span class="badge bg-danger">{{ t('serial_keys.status_revoked') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ t('serial_keys.status_expired') }}</span>
                                    @endif
                                </td>
                                <td>{{ $key->domain ?? t('serial_keys.not_specified') }}</td>
                                <td>{{ $key->ip_address ?? t('serial_keys.not_specified') }}</td>
                                <td>{{ $key->expires_at ? $key->expires_at->format('d/m/Y') : t('serial_keys.no_expiration') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.serial-keys.show', $key) }}" class="btn btn-sm btn-info" title="{{ t('serial_keys.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.serial-keys.edit', $key) }}" class="btn btn-sm btn-primary" title="{{ t('serial_keys.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($key->status === 'active')
                                            <form action="{{ route('admin.serial-keys.suspend', $key) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('{{ t('serial_keys.confirm_suspend') }}')" title="{{ t('serial_keys.suspend') }}">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.serial-keys.revoke', $key) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ t('serial_keys.confirm_revoke') }}')" title="{{ t('serial_keys.revoke') }}">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">{{ t('serial_keys.no_keys') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $serialKeys->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection