@extends('admin.layouts.app')

@section('title', t('projects.edit.title'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('projects.edit.title') }}</h1>
        <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ t('projects.edit.form_title') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Nom -->
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ t('projects.edit.name_label') }}</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $project->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ t('projects.edit.description_label') }}</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description', $project->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- URL du site -->
                        <div class="mb-3">
                            <label for="website_url" class="form-label">{{ t('projects.edit.website_url_label') }}</label>
                            <input type="url" id="website_url" name="website_url" class="form-control @error('website_url') is-invalid @enderror" 
                                   value="{{ old('website_url', $project->website_url) }}" placeholder="{{ t('projects.edit.website_url_placeholder') }}">
                            @error('website_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Statut -->
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ t('projects.edit.status_label') }}</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>{{ t('projects.edit.status_active') }}</option>
                                <option value="inactive" {{ old('status', $project->status) === 'inactive' ? 'selected' : '' }}>{{ t('projects.edit.status_inactive') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ t('projects.edit.submit_button') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection