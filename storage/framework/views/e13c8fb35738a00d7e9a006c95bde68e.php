<?php $__env->startSection('title', 'Gestion des templates'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">Templates d'email</h1>
                <a href="<?php echo e(route('admin.email.templates.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau template
                </a>
            </div>
        </div>
    </div>

    <!-- Liste des templates -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Sujet</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($template->name); ?></td>
                                    <td><?php echo e($template->subject); ?></td>
                                    <td><?php echo e($template->description); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo e(route('admin.email.templates.edit', $template)); ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?php echo e(route('admin.email.templates.preview', $template)); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTemplate(<?php echo e($template->id); ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Variables disponibles -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Variables disponibles</h5>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addVariable()">
                            <i class="fas fa-plus me-2"></i>Ajouter une variable
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="variablesTable">
                            <thead>
                                <tr>
                                    <th>Variable</th>
                                    <th>Description</th>
                                    <th>Exemple</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-id="1">
                                    <td><code>{name}</code></td>
                                    <td>Nom du destinataire</td>
                                    <td>John Doe</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editVariable(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariable(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-id="2">
                                    <td><code>{email}</code></td>
                                    <td>Adresse email du destinataire</td>
                                    <td>john@example.com</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editVariable(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariable(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-id="3">
                                    <td><code>{company}</code></td>
                                    <td>Nom de l'entreprise</td>
                                    <td>ACME Corp</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editVariable(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariable(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-id="4">
                                    <td><code>{date}</code></td>
                                    <td>Date courante</td>
                                    <td>01/01/2024</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editVariable(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariable(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-id="5">
                                    <td><code>{unsubscribe_link}</code></td>
                                    <td>Lien de désabonnement</td>
                                    <td>https://example.com/unsubscribe</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="editVariable(this)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteVariable(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function deleteTemplate(templateId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce template ?')) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `<?php echo e(route('admin.email.templates.index')); ?>/${templateId}`;
    form.style.display = 'none';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;

    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';

    form.appendChild(csrfToken);
    form.appendChild(methodField);
    document.body.appendChild(form);
    form.submit();
}

function addVariable() {
    const tbody = document.querySelector('#variablesTable tbody');
    const tr = document.createElement('tr');
    tr.dataset.isNew = 'true';
    tr.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" placeholder="nom_variable"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Description"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Exemple"></td>
        <td>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-success" onclick="saveVariable(this)">
                    <i class="fas fa-save"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="cancelAdd(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </td>
    `;
    tbody.insertBefore(tr, tbody.firstChild);
}

function editVariable(btn) {
    const row = btn.closest('tr');
    const name = row.querySelector('code').textContent;
    const description = row.children[1].textContent;
    const example = row.children[2].textContent;

    row.innerHTML = `
        <td><input type="text" class="form-control form-control-sm" value="${name}"></td>
        <td><input type="text" class="form-control form-control-sm" value="${description}"></td>
        <td><input type="text" class="form-control form-control-sm" value="${example}"></td>
        <td>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-success" onclick="saveVariable(this)">
                    <i class="fas fa-save"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="cancelEdit(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </td>
    `;
}

function saveVariable(btn) {
    const row = btn.closest('tr');
    const isNew = row.dataset.isNew === 'true';
    const inputs = row.querySelectorAll('input');
    const data = {
        name: inputs[0].value,
        description: inputs[1].value,
        example: inputs[2].value
    };

    if (isNew) {
        fetch('<?php echo e(route("admin.email.variables.store")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la sauvegarde : ' + data.message);
            }
        });
    } else {
        const variableId = row.dataset.id;
        fetch(`<?php echo e(route("admin.email.variables.index")); ?>/${variableId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la mise à jour : ' + data.message);
            }
        });
    }
}

function deleteVariable(btn) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette variable ?')) {
        return;
    }

    const row = btn.closest('tr');
    const variableId = row.dataset.id;

    fetch(`<?php echo e(route("admin.email.variables.index")); ?>/${variableId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            row.remove();
        } else {
            alert('Erreur lors de la suppression : ' + data.message);
        }
    });
}

function cancelAdd(btn) {
    btn.closest('tr').remove();
}

function cancelEdit(btn) {
    location.reload();
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/email/templates/index.blade.php ENDPATH**/ ?>