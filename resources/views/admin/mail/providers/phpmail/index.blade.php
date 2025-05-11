@extends('admin.layouts.app')

@section('title', t('email.providers.phpmail.title'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">{{ t('email.providers.phpmail.title') }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ t('email.providers.phpmail.settings') }}</h5>
                    <form action="{{ route('admin.mail.providers.phpmail.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="from_address" class="form-label">{{ t('email.from_address') }}</label>
                            <input type="email" name="from_address" id="from_address" class="form-control @error('from_address') is-invalid @enderror" value="{{ old('from_address', $config->from_address ?? '') }}" required>
                            @error('from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="from_name" class="form-label">{{ t('email.from_name') }}</label>
                            <input type="text" name="from_name" id="from_name" class="form-control @error('from_name') is-invalid @enderror" value="{{ old('from_name', $config->from_name ?? '') }}" required>
                            @error('from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ t('email.providers.phpmail.statistics') }}</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">{{ t('email.providers.phpmail.emails_sent_today') }}</h6>
                            <small class="text-muted">{{ $stats->daily_count ?? 0 }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $stats->daily_count ?? 0 }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">{{ t('email.providers.phpmail.emails_sent_month') }}</h6>
                            <small class="text-muted">{{ $stats->monthly_count ?? 0 }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $stats->monthly_count ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ t('email.providers.phpmail.logs') }}</h5>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <form action="{{ route('admin.mail.providers.phpmail.logs.clear') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash me-2"></i> {{ t('email.providers.phpmail.clear_logs') }}
                            </button>
                        </form>
                    </div>
                    <div class="logs-container" style="max-height: 300px; overflow-y: auto;">
                        @forelse($logs ?? [] as $log)
                            <div class="log-entry border-bottom py-2">
                                <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                                <div>{{ $log->message }}</div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ t('email.providers.phpmail.no_logs_available') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function testConnection() {
        if (!confirm('Voulez-vous envoyer un email de test ?')) {
            return;
        }

        fetch('{{ route("admin.mail.providers.phpmail.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email de test envoyé avec succès !');
            } else {
                alert('Erreur lors de l\'envoi de l\'email de test : ' + data.message);
            }
        })
        .catch(error => {
            alert('Erreur lors de l\'envoi de l\'email de test');
            console.error('Error:', error);
        });
    }
</script>
@endpush
@endsection