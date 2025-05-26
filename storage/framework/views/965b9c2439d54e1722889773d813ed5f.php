<?php $__env->startSection('title', t('serial_keys.edit_key')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo e(t('serial_keys.edit_key')); ?></h1>
        <a href="<?php echo e(route('admin.serial-keys.show', $serialKey)); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo e(t('common.back')); ?>

        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo e(t('serial_keys.information')); ?></h3>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('admin.serial-keys.update', $serialKey)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <!-- Projet (non modifiable) -->
                        <div class="mb-3">
                            <label class="form-label"><?php echo e(t('serial_keys.project')); ?></label>
                            <div class="form-control-plaintext">
                                <?php echo e($serialKey->project->name); ?>

                            </div>
                            <input type="hidden" name="project_id" value="<?php echo e($serialKey->project_id); ?>">
                        </div>
                        
                        <!-- Statut -->
                        <div class="mb-3">
                            <label for="status" class="form-label"><?php echo e(t('serial_keys.status')); ?></label>
                            <select id="status" name="status" class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="active" <?php echo e(old('status', $serialKey->status) === 'active' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_active')); ?></option>
                                <option value="suspended" <?php echo e(old('status', $serialKey->status) === 'suspended' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_suspended')); ?></option>
                                <option value="revoked" <?php echo e(old('status', $serialKey->status) === 'revoked' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_revoked')); ?></option>
                                <option value="expired" <?php echo e(old('status', $serialKey->status) === 'expired' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_expired')); ?></option>
                            </select>
                            <?php $__errorArgs = ['status'];
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
                    </div>

                    <div class="col-md-6">
                        <!-- Domaine -->
                        <div class="mb-3">
                            <label for="domain" class="form-label"><?php echo e(t('serial_keys.domain')); ?></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['domain'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="domain" name="domain" value="<?php echo e(old('domain', $serialKey->domain)); ?>">
                            <?php $__errorArgs = ['domain'];
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

                        <!-- Adresse IP -->
                        <div class="mb-3">
                            <label for="ip_address" class="form-label"><?php echo e(t('serial_keys.ip_address')); ?></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['ip_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="ip_address" name="ip_address" value="<?php echo e(old('ip_address', $serialKey->ip_address)); ?>">
                            <?php $__errorArgs = ['ip_address'];
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

                        <!-- Date d'expiration -->
                        <div class="mb-3">
                            <label for="expires_at" class="form-label"><?php echo e(t('serial_keys.expiration_date')); ?></label>
                            <input type="date" class="form-control <?php $__errorArgs = ['expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="expires_at" name="expires_at" value="<?php echo e(old('expires_at', $serialKey->expires_at ? $serialKey->expires_at->format('Y-m-d') : '')); ?>">
                            <?php $__errorArgs = ['expires_at'];
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
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo e(t('common.save')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/serial-keys/edit.blade.php ENDPATH**/ ?>