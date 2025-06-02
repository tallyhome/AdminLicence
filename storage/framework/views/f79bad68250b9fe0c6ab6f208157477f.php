<?php $__env->startSection('title', t('serial_keys.details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo e(t('serial_keys.details')); ?></h1>
        <div class="btn-group">
            <a href="<?php echo e(route('admin.serial-keys.edit', $serialKey)); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> <?php echo e(t('serial_keys.edit')); ?>

            </a>
            <?php if($serialKey->status === 'active'): ?>
                <form action="<?php echo e(route('admin.serial-keys.suspend', $serialKey)); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <button type="submit" class="btn btn-warning" onclick="return confirm('<?php echo e(t('serial_keys.confirm_suspend')); ?>')">
                        <i class="fas fa-pause"></i> <?php echo e(t('serial_keys.suspend')); ?>

                    </button>
                </form>
            <?php elseif($serialKey->status === 'suspended'): ?>
                <form action="<?php echo e(route('admin.serial-keys.revoke', $serialKey)); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo e(t('serial_keys.confirm_revoke')); ?>')">
                        <i class="fas fa-ban"></i> <?php echo e(t('serial_keys.revoke')); ?>

                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo e(t('serial_keys.information')); ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4"><?php echo e(t('serial_keys.license_key')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($serialKey->serial_key); ?></dd>

                        <dt class="col-sm-4"><?php echo e(t('serial_keys.project')); ?></dt>
                        <dd class="col-sm-8">
                            <a href="<?php echo e(route('admin.projects.show', $serialKey->project)); ?>">
                                <?php echo e($serialKey->project->name); ?>

                            </a>
                        </dd>

                        <dt class="col-sm-4"><?php echo e(t('serial_keys.status')); ?></dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?php echo e($serialKey->status === 'active' ? 'success' : ($serialKey->status === 'suspended' ? 'warning' : 'danger')); ?>">
                                <?php echo e(t('serial_keys.status_' . $serialKey->status)); ?>

                            </span>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4"><?php echo e(t('serial_keys.domain')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($serialKey->domain ?? t('serial_keys.not_specified')); ?></dd>

                        <dt class="col-sm-4"><?php echo e(t('serial_keys.ip_address')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($serialKey->ip_address ?? t('serial_keys.not_specified')); ?></dd>

                        <dt class="col-sm-4"><?php echo e(t('serial_keys.expiration_date')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($serialKey->expires_at ? $serialKey->expires_at->format('d/m/Y') : t('serial_keys.no_expiration')); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/serial-keys/show.blade.php ENDPATH**/ ?>