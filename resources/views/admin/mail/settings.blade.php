@extends('admin.layouts.app')

@section('title', t('email.title'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ t('email.title') }}</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> {{ t('common.back') }}
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ t('email.settings') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.mail.settings.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Driver -->
                        <div class="mb-3">
                            <label for="mail_driver" class="form-label">{{ t('email.driver') }}</label>
                            <select id="mail_driver" name="mail_driver" class="form-select @error('mail_driver') is-invalid @enderror" required>
                                <option value="smtp" {{ old('mail_driver', $settings['mail_driver']) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="mailgun" {{ old('mail_driver', $settings['mail_driver']) === 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ old('mail_driver', $settings['mail_driver']) === 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                <option value="postmark" {{ old('mail_driver', $settings['mail_driver']) === 'postmark' ? 'selected' : '' }}>Postmark</option>
                            </select>
                            @error('mail_driver')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Host -->
                        <div class="mb-3">
                            <label for="mail_host" class="form-label">{{ t('email.host') }}</label>
                            <input type="text" id="mail_host" name="mail_host" class="form-control @error('mail_host') is-invalid @enderror" 
                                   value="{{ old('mail_host', $settings['mail_host']) }}" required>
                            @error('mail_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Port -->
                        <div class="mb-3">
                            <label for="mail_port" class="form-label">{{ t('email.port') }}</label>
                            <input type="number" id="mail_port" name="mail_port" class="form-control @error('mail_port') is-invalid @enderror" 
                                   value="{{ old('mail_port', $settings['mail_port']) }}" required>
                            @error('mail_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Encryption -->
                        <div class="mb-3">
                            <label for="mail_encryption" class="form-label">{{ t('email.encryption') }}</label>
                            <select id="mail_encryption" name="mail_encryption" class="form-select @error('mail_encryption') is-invalid @enderror" required>
                                <option value="tls" {{ old('mail_encryption', $settings['mail_encryption']) === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption']) === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            @error('mail_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Username -->
                        <div class="mb-3">
                            <label for="mail_username" class="form-label">{{ t('email.username') }}</label>
                            <input type="text" id="mail_username" name="mail_username" class="form-control @error('mail_username') is-invalid @enderror" 
                                   value="{{ old('mail_username', $settings['mail_username']) }}" required>
                            @error('mail_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="mail_password" class="form-label">{{ t('email.password') }}</label>
                            <input type="password" id="mail_password" name="mail_password" class="form-control @error('mail_password') is-invalid @enderror" 
                                   value="{{ old('mail_password', $settings['mail_password']) }}" required>
                            @error('mail_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- From Address -->
                        <div class="mb-3">
                            <label for="mail_from_address" class="form-label">{{ t('email.from_address') }}</label>
                            <input type="email" id="mail_from_address" name="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" 
                                   value="{{ old('mail_from_address', $settings['mail_from_address']) }}" required>
                            @error('mail_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- From Name -->
                        <div class="mb-3">
                            <label for="mail_from_name" class="form-label">{{ t('email.from_name') }}</label>
                            <input type="text" id="mail_from_name" name="mail_from_name" class="form-control @error('mail_from_name') is-invalid @enderror" 
                                   value="{{ old('mail_from_name', $settings['mail_from_name']) }}" required>
                            @error('mail_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ t('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection