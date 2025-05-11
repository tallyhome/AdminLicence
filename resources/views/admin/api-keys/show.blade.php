@extends('admin.layouts.app')

@section('title', t('api_keys.details'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ t('api_keys.details') }}</h1>
        <div>
            @if($apiKey->is_active)
            <form action="{{ route('admin.api-keys.revoke', $apiKey) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-warning" onclick="return confirm('{{ t('api_keys.confirm_revoke') }}')">
                    <i class="fas fa-ban"></i> {{ t('api_keys.revoke') }}
                </button>
            </form>
            @elseif($apiKey->is_revoked)
            <form action="{{ route('admin.api-keys.reactivate', $apiKey) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success" onclick="return confirm('{{ t('api_keys.confirm_reactivate') }}')">
                    <i class="fas fa-check"></i> {{ t('api_keys.reactivate') }}
                </button>
            </form>
            @endif
            <form action="{{ route('admin.api-keys.destroy', $apiKey) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('{{ t('api_keys.confirm_delete') }}')">
                    <i class="fas fa-trash"></i> {{ t('common.delete') }}
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Informations de base -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ t('api_keys.basic_info') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <tr>
                                <th width="30%">{{ t('api_keys.name') }}</th>
                                <td>{{ $apiKey->name }}</td>
                            </tr>
                            <tr>
                                <th>{{ t('api_keys.project') }}</th>
                                <td>{{ $apiKey->project->name }}</td>
                            </tr>
                            <tr>
                                <th>{{ t('api_keys.key') }}</th>
                                <td>
                                    <code>{{ $apiKey->key }}</code>
                                    <button class="btn btn-sm btn-outline-secondary copy-key" data-clipboard-text="{{ $apiKey->key }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ t('api_keys.secret') }}</th>
                                <td>
                                    <code>{{ $apiKey->secret }}</code>
                                    <button class="btn btn-sm btn-outline-secondary copy-secret" data-clipboard-text="{{ $apiKey->secret }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ t('api_keys.status') }}</th>
                                <td>
                                    @if($apiKey->is_active)
                                    <span class="badge badge-success">{{ t('api_keys.active') }}</span>
                                    @elseif($apiKey->is_revoked)
                                    <span class="badge badge-danger">{{ t('api_keys.revoked') }}</span>
                                    @elseif($apiKey->is_expired)
                                    <span class="badge badge-warning">{{ t('api_keys.expired') }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ t('api_keys.expiration_date') }}</th>
                                <td>{{ $apiKey->expires_at ? $apiKey->expires_at->format('d/m/Y H:i') : t('api_keys.none') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques d'utilisation -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ t('api_keys.usage_stats') }}</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <tr>
                                <th width="30%">{{ t('api_keys.total_usage') }}</th>
                                <td>{{ $stats['total_usage'] }}</td>
                            </tr>
                            <tr>
                                <th>{{ t('api_keys.last_used') }}</th>
                                <td>{{ $stats['last_used'] ? $stats['last_used']->diffForHumans() : t('api_keys.never') }}</td>
                            </tr>
                            <tr>
                                <th>{{ t('api_keys.created_at') }}</th>
                                <td>{{ $stats['created_at']->format('d/m/Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ t('api_keys.permissions') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.api-keys.update-permissions', $apiKey) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="row">
                    @foreach(config('api.permissions') as $permission => $description)
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="{{ $permission }}" id="permission_{{ $permission }}"
                                class="form-check-input" {{ in_array($permission, $apiKey->permissions ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label" for="permission_{{ $permission }}">
                                {{ t($description) }}
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ t('api_keys.save_permissions') }}
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script>
    new ClipboardJS('.copy-key');
    new ClipboardJS('.copy-secret');
</script>
@endpush
@endsection 