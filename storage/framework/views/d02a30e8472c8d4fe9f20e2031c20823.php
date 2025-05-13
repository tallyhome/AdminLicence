<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo e(t('translations.manage_translations')); ?></h1>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-language me-1"></i>
                <?php echo e(t('translations.available_languages')); ?>

            </div>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="searchTranslation" class="form-control form-control-sm me-2" style="width: 250px;" placeholder="🔍 <?php echo e(t('translations.key')); ?> / <?php echo e(t('translations.translation')); ?>">
                <select id="languageSelect" class="form-select form-select-sm me-2" style="width: 120px;">
                    <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($lang); ?>" <?php echo e($lang == request()->query('lang', $languages[0]) ? 'selected' : ''); ?>><?php echo e(strtoupper($lang)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newTranslationModal">
                    <i class="fas fa-plus me-1"></i> <?php echo e(t('translations.add_new')); ?>

                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="accordion" id="translationsAccordion">
                <?php
                    $groupedTranslations = $translations->groupBy(function($item) {
                        $parts = explode('.', $item['key']);
                        return $parts[0];
                    });
                ?>

                <?php $__currentLoopData = $groupedTranslations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $sectionTranslations): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo e($section); ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?php echo e($section); ?>" aria-expanded="false" 
                                    aria-controls="collapse<?php echo e($section); ?>">
                                <i class="fas fa-folder me-2"></i>
                                <?php echo e(ucfirst($section)); ?>

                                <span class="badge bg-primary ms-2"><?php echo e(count($sectionTranslations)); ?></span>
                            </button>
                        </h2>
                        <div id="collapse<?php echo e($section); ?>" class="accordion-collapse collapse" 
                             aria-labelledby="heading<?php echo e($section); ?>" data-bs-parent="#translationsAccordion">
                            <div class="accordion-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 35%"><?php echo e(t('translations.key')); ?></th>
                                                <th><?php echo e(t('translations.translation')); ?></th>
                                                <th style="width: 100px"><?php echo e(t('translations.actions')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $sectionTranslations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $translation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="translation-row">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-key text-muted me-2"></i>
                                                            <div>
                                                                <code class="bg-light px-2 py-1 rounded"><?php echo e($translation['key']); ?></code>
                                                                <small class="text-muted d-block mt-1">
                                                                    <?php echo e(implode(' > ', explode('.', $translation['key']))); ?>

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
                                               data-lang="<?php echo e($translation['lang']); ?>"
                                               data-file="translation"
                                               data-key="<?php echo e($translation['key']); ?>"
                                               value="<?php echo e($translation['value']); ?>"
                                               placeholder="<?php echo e(t('translations.enter_translation')); ?>">
                                        <button class="btn btn-outline-primary save-translation" type="button" title="<?php echo e(t('common.save')); ?>">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-danger btn-sm delete-translation" title="<?php echo e(t('common.delete')); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    <?php echo e(t('common.showing')); ?> <?php echo e(count($sectionTranslations)); ?> <?php echo e(t('common.of')); ?> <?php echo e(count($sectionTranslations)); ?> <?php echo e(t('translations.entries')); ?>

                </div>
            </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une nouvelle traduction -->
<div class="modal fade" id="newTranslationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e(t('translations.add_new_translation')); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newTranslationForm">
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(t('translations.file')); ?></label>
                        <input type="text" class="form-control" name="file" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo e(t('translations.key')); ?></label>
                        <input type="text" class="form-control" name="key" required>
                    </div>
                    <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo e(strtoupper($lang)); ?></label>
                            <input type="text" class="form-control" name="translations[<?php echo e($lang); ?>]" required>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(t('common.cancel')); ?></button>
                <button type="button" class="btn btn-primary" id="saveNewTranslation"><?php echo e(t('common.save')); ?></button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/translations/index.blade.php ENDPATH**/ ?>