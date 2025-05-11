@extends('admin.layouts.app')

@section('title', t('projects.management'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('projects.management') }}</h1>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ t('projects.new_project') }}
        </a>
    </div>

    @if($projects->isEmpty())
        <div class="alert alert-info">
            {{ t('projects.no_projects') }}
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ t('common.name') }}</th>
                        <th>{{ t('common.description') }}</th>
                        <th>{{ t('projects.license_keys') }}</th>
                        <th>{{ t('projects.api_keys') }}</th>
                        <th>{{ t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->description ?? t('common.no_description') }}</td>
                            <td>{{ $project->serialKeys->count() }}</td>
                            <td>{{ $project->apiKeys->count() }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-info" title="{{ t('common.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-primary" title="{{ t('common.edit') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ t('projects.confirm_delete') }}')" title="{{ t('common.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection