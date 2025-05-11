@extends('admin.layouts.app')

@section('title', t('serial_keys.details'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('serial_keys.details') }}</h1>
        <div class="btn-group">
            <a href="{{ route('admin.serial-keys.edit', $serialKey) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ t('serial_keys.edit') }}
            </a>
            @if($serialKey->status === 'active')
                <form action="{{ route('admin.serial-keys.suspend', $serialKey) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-warning" onclick="return confirm('{{ t('serial_keys.confirm_suspend') }}')">
                        <i class="fas fa-pause"></i> {{ t('serial_keys.suspend') }}
                    </button>
                </form>
            @elseif($serialKey->status === 'suspended')
                <form action="{{ route('admin.serial-keys.revoke', $serialKey) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('{{ t('serial_keys.confirm_revoke') }}')">
                        <i class="fas fa-ban"></i> {{ t('serial_keys.revoke') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ t('serial_keys.information') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">{{ t('serial_keys.license_key') }}</dt>
                        <dd class="col-sm-8">{{ $serialKey->serial_key }}</dd>

                        <dt class="col-sm-4">{{ t('serial_keys.project') }}</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('admin.projects.show', $serialKey->project) }}">
                                {{ $serialKey->project->name }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">{{ t('serial_keys.status') }}</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $serialKey->status === 'active' ? 'success' : ($serialKey->status === 'suspended' ? 'warning' : 'danger') }}">
                                {{ t('serial_keys.status_' . $serialKey->status) }}
                            </span>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">{{ t('serial_keys.domain') }}</dt>
                        <dd class="col-sm-8">{{ $serialKey->domain ?? t('serial_keys.not_specified') }}</dd>

                        <dt class="col-sm-4">{{ t('serial_keys.ip_address') }}</dt>
                        <dd class="col-sm-8">{{ $serialKey->ip_address ?? t('serial_keys.not_specified') }}</dd>

                        <dt class="col-sm-4">{{ t('serial_keys.expiration_date') }}</dt>
                        <dd class="col-sm-8">{{ $serialKey->expires_at ? $serialKey->expires_at->format('d/m/Y') : t('serial_keys.no_expiration') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection