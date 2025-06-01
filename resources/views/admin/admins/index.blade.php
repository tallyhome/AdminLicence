@extends('admin.layouts.app')

@section('title', t('admins.title'))

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">{{ t('admins.title') }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ t('common.dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ t('admins.administrators') }}</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-users-cog me-1"></i>
                {{ t('admins.list') }}
            </div>
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> {{ t('admins.new_admin') }}
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="adminsTable">
                    <thead>
                        <tr>
                            <th>{{ t('admins.id') }}</th>
                            <th>{{ t('common.name') }}</th>
                            <th>{{ t('common.email') }}</th>
                            <th>{{ t('admins.role') }}</th>
                            <th>{{ t('admins.creation_date') }}</th>
                            <th>{{ t('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                            <tr>
                                <td>{{ $admin->id }}</td>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>
                                    @if($admin->role === 'superadmin')
                                        <span class="badge bg-danger">{{ t('admins.super_admin') }}</span>
                                    @else
                                        <span class="badge bg-primary">{{ t('admins.admin') }}</span>
                                    @endif
                                </td>
                                <td>{{ $admin->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.admins.edit', $admin->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($admin->id !== auth()->id())
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $admin->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Modal de confirmation de suppression -->
                                    <div class="modal fade" id="deleteModal{{ $admin->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $admin->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{{ $admin->id }}">{{ t('admins.delete_confirmation') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ t('admins.delete_confirm_message', ['name' => $admin->name]) }}
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
                                                    <form action="{{ route('admin.admins.destroy', $admin->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">{{ t('common.delete') }}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#adminsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[0, 'desc']]
        });
    });
</script>
@endsection
