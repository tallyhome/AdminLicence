@extends('layouts.admin')

@section('title', 'Ajouter une traduction')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Ajouter une traduction</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-language me-1"></i>
            Nouvelle traduction
        </div>
        <div class="card-body">
            <form id="addTranslationForm" action="{{ route('admin.settings.translations.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="language" class="form-label">Langue</label>
                    <select name="language" id="language" class="form-select" required>
                        @foreach($languages as $lang)
                            <option value="{{ $lang }}" {{ $lang === $currentLanguage ? 'selected' : '' }}>
                                {{ strtoupper($lang) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="file" class="form-label">Fichier</label>
                    <input type="text" class="form-control" id="file" name="file" required>
                    <div class="form-text">Nom du fichier ou section (ex: common, validation, etc.)</div>
                </div>
                
                <div class="mb-3">
                    <label for="key" class="form-label">Clé</label>
                    <input type="text" class="form-control" id="key" name="key" required>
                    <div class="form-text">Identifiant unique pour cette traduction</div>
                </div>
                
                <div class="mb-3">
                    <label for="value" class="form-label">Traduction</label>
                    <input type="text" class="form-control" id="value" name="value" required>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.settings.translations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#addTranslationForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                success: function(response) {
                    toastr.success(response.message);
                    setTimeout(function() {
                        window.location.href = '{{ route("admin.settings.translations.index") }}';
                    }, 1000);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Une erreur est survenue');
                }
            });
        });
    });
</script>
@endpush