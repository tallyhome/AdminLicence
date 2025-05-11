@extends('admin.layouts.app')

@section('title', t('api_keys.create'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ t('api_keys.create') }}</h1>
        <a href="{{ route('admin.api-keys.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.api-keys.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="project_id">{{ t('api_keys.project') }}</label>
                            <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                                <option value="">{{ t('api_keys.select_project') }}</option>
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
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">{{ t('api_keys.key_name') }}</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="expires_at">{{ t('api_keys.expiration_date') }}</label>
                            <input type="datetime-local" name="expires_at" id="expires_at"
                                class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                            <small class="form-text text-muted">{{ t('api_keys.no_expiration_hint') }}</small>
                            @error('expires_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h5>{{ t('api_keys.permissions') }}</h5>
                        <div class="row">
                            @foreach(config('api.permissions') as $permission => $description)
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                        id="permission_{{ $permission }}" class="form-check-input"
                                        {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_{{ $permission }}">
                                        {{ t($description) }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ t('api_keys.create') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 