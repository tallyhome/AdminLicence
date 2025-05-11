@extends('admin.layouts.app')

@section('title', t('projects.show.title'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('projects.show.title') }}</h1>
        <div class="btn-group">
            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
            </a>
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> {{ t('common.edit') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('projects.show.details') }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">{{ t('projects.show.name') }}</dt>
                        <dd class="col-sm-8">{{ $project->name }}</dd>

                        <dt class="col-sm-4">{{ t('projects.show.description') }}</dt>
                        <dd class="col-sm-8">{{ $project->description ?? t('common.no_description') }}</dd>

                        <dt class="col-sm-4">{{ t('projects.show.website_url') }}</dt>
                        <dd class="col-sm-8">
                            @if($project->website_url)
                                <a href="{{ $project->website_url }}" target="_blank">{{ $project->website_url }}</a>
                            @else
                                {{ t('common.not_specified') }}
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ t('projects.show.status') }}</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $project->status === 'active' ? 'success' : 'danger' }}">
                                {{ t('projects.show.status_' . $project->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">{{ t('projects.show.created_at') }}</dt>
                        <dd class="col-sm-8">{{ $project->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">{{ t('projects.show.updated_at') }}</dt>
                        <dd class="col-sm-8">{{ $project->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('projects.show.statistics') }}</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">{{ t('projects.show.total_license_keys') }}</dt>
                        <dd class="col-sm-6">{{ $project->serialKeys->count() }}</dd>

                        <dt class="col-sm-6">{{ t('projects.show.total_api_keys') }}</dt>
                        <dd class="col-sm-6">{{ $project->apiKeys->count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ t('projects.show.actions') }}</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group">
                        <a href="{{ route('admin.serial-keys.create', ['project_id' => $project->id]) }}" class="btn btn-primary">
                            <i class="fas fa-key"></i> {{ t('projects.show.create_license_key') }}
                        </a>
                        <a href="{{ route('admin.api-keys.create', ['project_id' => $project->id]) }}" class="btn btn-primary">
                            <i class="fas fa-key"></i> {{ t('projects.show.create_api_key') }}
                        </a>
                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ t('projects.show.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> {{ t('projects.show.delete_project') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection