@extends('layouts.admin')

@section('title', 'Gestion des traductions')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mt-4">{{ trans('translations.title') }}</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTranslationModal">
                    <i class="fas fa-plus"></i> {{ trans('translations.add_translation') }}
                </button>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="small">Langues disponibles</div>
                                <div class="h3 mb-0">{{ count($languages) }}</div>
                            </div>
                            <div class="small">
                                <i class="fas fa-language fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-language me-1"></i>
                        {{ trans('translations.available_translations') }}
                    </div>
                    <div class="d-flex w-50 gap-2">
                        <input type="text" id="searchTranslation" class="form-control form-control-sm" placeholder="🔍 {{ trans('translations.key') }} / {{ trans('translations.translation') }}">
                        <select id="languageSelect" class="form-select form-select-sm w-auto">
                            @foreach($languages as $lang)
                                <option value="{{ $lang }}">{{ strtoupper($lang) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0" id="translationsTable">
                    <thead class="table-light">
                        <tr>
                            <th>{{ trans('translations.file') }}</th>
                            <th>{{ trans('translations.key') }}</th>
                            <th>{{ trans('translations.translation') }}</th>
                            <th class="text-end">{{ trans('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>

<!-- Modal d'ajout de traduction -->
<div class="modal fade" id="addTranslationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>Ajouter une traduction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <form id="addTranslationForm" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="newFile" class="form-label">{{ trans('translations.file') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                            <input type="text" class="form-control" id="newFile" required placeholder="{{ trans('translations.file_placeholder') }}">
                        </div>
                        <div class="invalid-feedback">{{ trans('translations.file_required') }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="newKey" class="form-label">{{ trans('translations.key') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="text" class="form-control" id="newKey" required placeholder="{{ trans('translations.key_placeholder') }}">
                        </div>
                        <div class="invalid-feedback">{{ trans('translations.key_required') }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="newValue" class="form-label">{{ trans('translations.translation') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-language"></i></span>
                            <input type="text" class="form-control" id="newValue" required placeholder="{{ trans('translations.value_placeholder') }}">
                        </div>
                        <div class="invalid-feedback">{{ trans('translations.value_required') }}</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>{{ trans('common.cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="saveNewTranslation">
                    <i class="fas fa-save me-1"></i>{{ trans('common.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        function loadTranslations(language, search = '') {
            const translations = @json($translations);
            const tbody = $('#translationsTable tbody');
            tbody.empty();

            if (translations[language]) {
                Object.entries(translations[language]).forEach(([file, keys]) => {
                    Object.entries(keys).forEach(([key, value]) => {
                        const searchText = (file + ' ' + key + ' ' + value).toLowerCase();
                        if (!search || searchText.includes(search.toLowerCase())) {
                            const tr = $('<tr>');
                            tr.append($('<td>').text(file));
                            tr.append($('<td>').text(key));
                            tr.append($('<td>').append(
                                $('<input>')
                                    .addClass('form-control translation-value')
                                    .val(value)
                                    .data('file', file)
                                    .data('key', key)
                            ));
                            tr.append($('<td>').append(
                                $('<button>')
                                    .addClass('btn btn-danger btn-sm delete-translation')
                                    .html('<i class="fas fa-trash"></i>')
                                    .data('file', file)
                                    .data('key', key)
                            ));
                            tbody.append(tr);
                        }
                    });
                });
            }
        }

        $('#languageSelect').change(function() {
            loadTranslations($(this).val(), $('#searchTranslation').val());
        });

        $('#searchTranslation').on('input', function() {
            loadTranslations($('#languageSelect').val(), $(this).val());
        });

        // Charger les traductions initiales
        loadTranslations($('#languageSelect').val());

        // Mettre à jour une traduction
        $(document).on('change', '.translation-value', function() {
            const input = $(this);
            const language = $('#languageSelect').val();
            const file = input.data('file');
            const key = input.data('key');
            const value = input.val();

            $.ajax({
                url: '{{ route("admin.translations.update") }}',
                method: 'PUT',
                data: { language, file, key, value },
                success: function(response) {
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Une erreur est survenue');
                    loadTranslations(language); // Recharger en cas d'erreur
                }
            });
        });

        // Supprimer une traduction
        $(document).on('click', '.delete-translation', function() {
            const button = $(this);
            const language = $('#languageSelect').val();
            const file = button.data('file');
            const key = button.data('key');

            if (confirm('Êtes-vous sûr de vouloir supprimer cette traduction ?')) {
                $.ajax({
                    url: '{{ route("admin.translations.destroy") }}',
                    method: 'DELETE',
                    data: { language, file, key },
                    success: function(response) {
                        toastr.success(response.message);
                        loadTranslations(language);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.error || 'Une erreur est survenue');
                    }
                });
            }
        });

        // Ajouter une nouvelle traduction
        $('#saveNewTranslation').click(function() {
            const language = $('#languageSelect').val();
            const file = $('#newFile').val();
            const key = $('#newKey').val();
            const value = $('#newValue').val();

            $.ajax({
                url: '{{ route("admin.translations.store") }}',
                method: 'POST',
                data: { language, file, key, value },
                success: function(response) {
                    toastr.success(response.message);
                    $('#addTranslationModal').modal('hide');
                    $('#addTranslationForm')[0].reset();
                    loadTranslations(language);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.error || 'Une erreur est survenue');
                }
            });
        });
    });
</script>
@endpush