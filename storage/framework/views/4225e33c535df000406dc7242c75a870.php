

<?php $__env->startSection('title', 'Configuration PHPMail'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">Configuration PHPMail</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Paramètres d'envoi</h5>
                    <form action="<?php echo e(route('admin.mail.providers.phpmail.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="mb-3">
                            <label for="from_address" class="form-label">Adresse d'expédition</label>
                            <input type="email" name="from_address" id="from_address" class="form-control <?php $__errorArgs = ['from_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('from_address', $config->from_address ?? '')); ?>" required>
                            <?php $__errorArgs = ['from_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="from_name" class="form-label">Nom d'expédition</label>
                            <input type="text" name="from_name" id="from_name" class="form-control <?php $__errorArgs = ['from_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('from_name', $config->from_name ?? '')); ?>" required>
                            <?php $__errorArgs = ['from_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Enregistrer
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="testConnection()">
                                <i class="fas fa-paper-plane me-2"></i> Tester la configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Statistiques</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-0">Emails envoyés aujourd'hui</h6>
                            <small class="text-muted"><?php echo e($stats->daily_count ?? 0); ?></small>
                        </div>
                        <span class="badge bg-primary rounded-pill"><?php echo e($stats->daily_count ?? 0); ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Emails envoyés ce mois</h6>
                            <small class="text-muted"><?php echo e($stats->monthly_count ?? 0); ?></small>
                        </div>
                        <span class="badge bg-primary rounded-pill"><?php echo e($stats->monthly_count ?? 0); ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Journaux</h5>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <form action="<?php echo e(route('admin.mail.providers.phpmail.logs.clear')); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash me-2"></i> Vider les journaux
                            </button>
                        </form>
                    </div>
                    <div class="logs-container" style="max-height: 300px; overflow-y: auto;">
                        <?php $__empty_1 = true; $__currentLoopData = $logs ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="log-entry border-bottom py-2">
                                <small class="text-muted"><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></small>
                                <div><?php echo e($log->message); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted mb-0">Aucun journal disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function testConnection() {
        if (!confirm('Voulez-vous envoyer un email de test ?')) {
            return;
        }

        fetch('<?php echo e(route("admin.mail.providers.phpmail.test")); ?>', {
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/mail/providers/phpmail/index.blade.php ENDPATH**/ ?>