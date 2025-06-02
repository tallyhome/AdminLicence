<?php $__env->startSection('title', t('api_keys.details')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(t('api_keys.details')); ?></h1>
        <div>
            <?php if($apiKey->is_active): ?>
            <form action="<?php echo e(route('admin.api-keys.revoke', $apiKey)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <button type="submit" class="btn btn-warning" onclick="return confirm('<?php echo e(t('api_keys.confirm_revoke')); ?>')">
                    <i class="fas fa-ban"></i> <?php echo e(t('api_keys.revoke')); ?>

                </button>
            </form>
            <?php elseif($apiKey->is_revoked): ?>
            <form action="<?php echo e(route('admin.api-keys.reactivate', $apiKey)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <button type="submit" class="btn btn-success" onclick="return confirm('<?php echo e(t('api_keys.confirm_reactivate')); ?>')">
                    <i class="fas fa-check"></i> <?php echo e(t('api_keys.reactivate')); ?>

                </button>
            </form>
            <?php endif; ?>
            <form action="<?php echo e(route('admin.api-keys.destroy', $apiKey)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo e(t('api_keys.confirm_delete')); ?>')">
                    <i class="fas fa-trash"></i> <?php echo e(t('common.delete')); ?>

                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Informations de base -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(t('api_keys.basic_info')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <tr>
                                <th width="30%"><?php echo e(t('api_keys.name')); ?></th>
                                <td><?php echo e($apiKey->name); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('api_keys.project')); ?></th>
                                <td><?php echo e($apiKey->project->name); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('api_keys.key')); ?></th>
                                <td>
                                    <code><?php echo e($apiKey->key); ?></code>
                                    <button class="btn btn-sm btn-outline-secondary copy-key" data-clipboard-text="<?php echo e($apiKey->key); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('api_keys.secret')); ?></th>
                                <td>
                                    <code><?php echo e($apiKey->secret); ?></code>
                                    <button class="btn btn-sm btn-outline-secondary copy-secret" data-clipboard-text="<?php echo e($apiKey->secret); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('api_keys.status')); ?></th>
                                <td>
                                    <?php if($apiKey->is_active): ?>
                                    <span class="badge badge-success"><?php echo e(t('api_keys.active')); ?></span>
                                    <?php elseif($apiKey->is_revoked): ?>
                                    <span class="badge badge-danger"><?php echo e(t('api_keys.revoked')); ?></span>
                                    <?php elseif($apiKey->is_expired): ?>
                                    <span class="badge badge-warning"><?php echo e(t('api_keys.expired')); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('api_keys.expiration_date')); ?></th>
                                <td><?php echo e($apiKey->expires_at ? $apiKey->expires_at->format('d/m/Y H:i') : t('api_keys.none')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques d'utilisation -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(t('api_keys.usage_stats')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <tr>
                                <th width="30%"><?php echo e(t('api_keys.total_usage')); ?></th>
                                <td><?php echo e($stats['total_usage']); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('api_keys.last_used')); ?></th>
                                <td><?php echo e($stats['last_used'] ? $stats['last_used']->diffForHumans() : t('api_keys.never')); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(t('api_keys.created_at')); ?></th>
                                <td><?php echo e($stats['created_at']->format('d/m/Y H:i')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(t('api_keys.permissions')); ?></h6>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('admin.api-keys.update-permissions', $apiKey)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <div class="row">
                    <?php $__currentLoopData = config('api.permissions'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission => $description): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="<?php echo e($permission); ?>" id="permission_<?php echo e($permission); ?>"
                                class="form-check-input" <?php echo e(in_array($permission, $apiKey->permissions ?? []) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="permission_<?php echo e($permission); ?>">
                                <?php echo e(t($description)); ?>

                            </label>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo e(t('api_keys.save_permissions')); ?>

                </button>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script>
    new ClipboardJS('.copy-key');
    new ClipboardJS('.copy-secret');
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/api-keys/show.blade.php ENDPATH**/ ?>