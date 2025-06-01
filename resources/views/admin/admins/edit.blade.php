@extends('admin.layouts.app')

@section('title', t('admins.edit.title'))

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">{{ t('admins.edit.title') }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ t('common.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}">{{ t('admins.administrators') }}</a></li>
        <li class="breadcrumb-item active">{{ t('admins.edit') }}</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit me-1"></i>
            {{ t('admins.edit.form_title') }}
        </div>
        <div class="card-body">
            <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">{{ t('admins.edit.name_label') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">{{ t('admins.edit.email_label') }}</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ t('admins.edit.password_label') }}</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">{{ t('admins.edit.password_confirmation_label') }}</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">{{ t('admins.edit.role_label') }}</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="admin" {{ (old('role', $admin->role) == 'admin') ? 'selected' : '' }}>{{ t('admins.admin') }}</option>
                        <option value="superadmin" {{ (old('role', $admin->role) == 'superadmin') ? 'selected' : '' }}>{{ t('admins.super_admin') }}</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">{{ t('common.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ t('admins.edit.submit_button') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
