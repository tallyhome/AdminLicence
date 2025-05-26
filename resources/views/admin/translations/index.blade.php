@extends('admin.layouts.app')

@push('styles')
<style>
    h1 {
        margin-block: 0.67em;
        font-size: 2em;
    }
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    .translation-row:hover {
        background-color: rgba(0,0,0,.02);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">{{ t('translations.manage_translations') }}</h1>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-language me-1"></i>
                {{ t('translations.available_languages') }}
            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchTranslation" class="form-control form-control-sm me-2" style="width: 250px;" placeholder="🔍 {{ t('translations.key') }} / {{ t('translations.translation') }}">
                <select id="languageSelect" class="form-select form-select-sm me-2" style="width: 120px;">
                    @foreach($languages as $lang)
                        <option value="{{ $lang }}" {{ $lang == request()->query('lang', $languages[0]) ? 'selected' : '' }}>{{ strtoupper($lang) }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newTranslationModal">
                    <i class="fas fa-plus me-1"></i> {{ t('translations.add_new') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="accordion" id="translationsAccordion">
                @foreach($sections as $section)
                    @php
                        $sectionTranslations = $groupedTranslations->get($section, collect([]));
                    @endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $section }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $section }}" aria-expanded="false" 
                                    aria-controls="collapse{{ $section }}">
                                <i class="fas fa-folder me-2"></i>
                                {{ ucfirst($section) }}
                                <span class="badge bg-primary ms-2">{{ count($sectionTranslations) }}</span>
                            </button>
                        </h2>
                        <div id="collapse{{ $section }}" class="accordion-collapse collapse" 
                             aria-labelledby="heading{{ $section }}" data-bs-parent="#translationsAccordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 35%">{{ t('translations.key') }}</th>
                                                <th>{{ t('translations.translation') }}</th>
                                                <th style="width: 100px">{{ t('translations.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sectionTranslations as $translation)
                                                <tr class="translation-row">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-key text-muted me-2"></i>
                                                            <div>
                                                                <code class="bg-light px-2 py-1 rounded">{{ $translation['key'] }}</code>
                                                                <small class="text-muted d-block mt-1">
                                                                    {{ implode(' > ', explode('.', $translation['key'])) }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </td>

                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-language"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control translation-input"
                                               data-lang="{{ $translation['lang'] }}"
                                               data-file="translation"
                                               data-key="{{ $translation['key'] }}"
                                               value="{{ $translation['value'] }}"
                                               placeholder="{{ t('translations.enter_translation') }}">
                                        <button class="btn btn-outline-primary save-translation" type="button" title="{{ t('common.save') }}">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-danger btn-sm delete-translation" title="{{ t('common.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    {{ t('common.showing') }} {{ $sectionTranslations->count() }} {{ t('translations.entries') }}
                </div>
            </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une nouvelle traduction -->
<div class="modal fade" id="newTranslationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('translations.add_new_translation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newTranslationForm">
                    <div class="mb-3">
                        <label class="form-label">{{ t('translations.file') }}</label>
                        <input type="text" class="form-control" name="file" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ t('translations.key') }}</label>
                        <input type="text" class="form-control" name="key" required>
                    </div>
                    @foreach($languages as $lang)
                        <div class="mb-3">
                            <label class="form-label">{{ strtoupper($lang) }}</label>
                            <input type="text" class="form-control" name="translations[{{ $lang }}]" required>
                        </div>
                    @endforeach
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="saveNewTranslation">{{ t('common.save') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const languageSelect = document.getElementById('languageSelect');
        const searchInput = document.getElementById('searchTranslation');
        const accordionItems = document.querySelectorAll('.accordion-item');
        let loadedSections = new Set();

        // Fonction pour charger les traductions d'une section
        function loadSectionTranslations(section) {
            if (loadedSections.has(section)) return;
            
            const rows = document.querySelectorAll(`#collapse${section} .translation-row`);
            rows.forEach(row => {
                const input = row.querySelector('.translation-input');
                if (input) {
                    input.addEventListener('change', function() {
                        row.classList.add('needs-save');
                    });
                }
            });
            
            loadedSections.add(section);
        }

        // Gestionnaire pour l'accordéon
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function() {
                const sectionId = this.getAttribute('aria-controls').replace('collapse', '');
                loadSectionTranslations(sectionId);
            });
        });

        // Gestionnaire pour le sélecteur de langue
        function filterTranslations(selectedLang) {
            document.querySelectorAll('.translation-row').forEach(row => {
                const input = row.querySelector(`[data-lang="${selectedLang}"]`);
                row.style.display = input ? '' : 'none';
            });
        }

        // Filtrer initialement avec la première langue
        filterTranslations(languageSelect.value);

        // Gestionnaire d'événements pour le changement de langue
        languageSelect.addEventListener('change', function() {
            // Rediriger vers la même page avec le paramètre de langue mis à jour
            window.location.href = `${window.location.pathname}?lang=${this.value}`;
        });

        // Gestionnaire pour sauvegarder les traductions
        document.querySelectorAll('.save-translation').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const lang = input.dataset.lang;
                const file = input.dataset.file;
                const key = input.dataset.key;
                const value = input.value;

                fetch('/admin/translations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ lang, file, key, value })
                })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        const text = await response.text();
                        showNotification('error', 'Erreur HTTP ' + response.status + ': ' + text);
                        return;
                    }
                    if (data.message) {
                        showNotification('success', data.message);
                    } else if (data.error) {
                        showNotification('error', data.error);
                    }
                })
                .catch(error => showNotification('error', error.message));
            });
        });

        // Gestionnaire pour le formulaire d'ajout de nouvelle traduction
        document.getElementById('saveNewTranslation').addEventListener('click', function() {
            const form = document.getElementById('newTranslationForm');
            const formData = new FormData(form);
            const data = {
                file: formData.get('file'),
                translations: {}
            };

            // Collecter toutes les traductions
            document.querySelectorAll('[name^="translations["]').forEach(input => {
                const lang = input.name.match(/\[(.*?)\]/)[1];
                data.translations[lang] = input.value;
            });

            fetch('/admin/translations/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    showNotification('success', data.message);
                    location.reload();
                } else if (data.error) {
                    showNotification('error', data.error);
                }
            })
            .catch(error => showNotification('error', error.message));
        });

        // Barre de recherche : filtrage dynamique
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const search = this.value.toLowerCase();
                document.querySelectorAll('.accordion-item').forEach(item => {
                    let found = false;
                    item.querySelectorAll('tbody tr').forEach(row => {
                        const key = row.querySelector('code')?.textContent?.toLowerCase() || '';
                        const value = row.querySelector('input.translation-input')?.value?.toLowerCase() || '';
                        if (key.includes(search) || value.includes(search)) {
                            row.style.display = '';
                            found = true;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    // Affiche ou masque la section selon le résultat
                    item.style.display = found ? '' : 'none';
                });
            });
        }

        // Fonction utilitaire pour afficher les notifications
        function showNotification(type, message) {
            // Utiliser le système de notification existant
            if (typeof window.showToast === 'function') {
                window.showToast(type, message);
            } else {
                alert(message);
            }
        }
    });
</script>
@endpush