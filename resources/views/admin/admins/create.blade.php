@extends('admin.layouts.app')

@section('title', t('admins.create.title'))

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">{{ t('admins.create.title') }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ t('common.dashboard') }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.admins.index') }}">{{ t('admins.administrators') }}</a></li>
        <li class="breadcrumb-item active">{{ t('admins.add') }}</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-plus me-1"></i>
            {{ t('admins.new_admin') }}
        </div>
        <div class="card-body">
            <form action="{{ route('admin.admins.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">{{ t('admins.create.name_label') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">{{ t('admins.create.email_label') }}</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">{{ t('admins.create.password_label') }}</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">{{ t('admins.create.password_confirmation_label') }}</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">{{ t('admins.create.role_label') }}</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>{{ t('admins.admin') }}</option>
                        <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>{{ t('admins.super_admin') }}</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">{{ t('common.cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ t('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
