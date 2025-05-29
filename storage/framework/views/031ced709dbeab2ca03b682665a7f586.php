<?php
use Illuminate\Support\Str;
?>

<?php $__env->startSection('title', t('api_keys.management')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(t('api_keys.management')); ?></h1>
        <a href="<?php echo e(route('admin.api-keys.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(t('api_keys.create')); ?>

        </a>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.api-keys.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="project_id" class="form-label"><?php echo e(t('api_keys.project')); ?></label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value=""><?php echo e(t('api_keys.all_projects')); ?></option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($project->id); ?>" <?php echo e(request('project_id') == $project->id ? 'selected' : ''); ?>>
                            <?php echo e($project->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label"><?php echo e(t('api_keys.status')); ?></label>
                    <select name="status" id="status" class="form-select">
                        <option value=""><?php echo e(t('api_keys.all_status')); ?></option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>><?php echo e(t('api_keys.active_plural')); ?></option>
                        <option value="revoked" <?php echo e(request('status') == 'revoked' ? 'selected' : ''); ?>><?php echo e(t('api_keys.revoked_plural')); ?></option>
                        <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>><?php echo e(t('api_keys.expired_plural')); ?></option>
                        <option value="used" <?php echo e(request('status') == 'used' ? 'selected' : ''); ?>><?php echo e(t('api_keys.used_plural')); ?></option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> <?php echo e(t('common.filter')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des clés API -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(t('api_keys.name')); ?></th>
                            <th><?php echo e(t('api_keys.project')); ?></th>
                            <th><?php echo e(t('api_keys.key')); ?></th>
                            <th><?php echo e(t('api_keys.status')); ?></th>
                            <th><?php echo e(t('api_keys.last_used')); ?></th>
                            <th><?php echo e(t('common.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $apiKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $apiKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($apiKey->name); ?></td>
                            <td><?php echo e($apiKey->project->name); ?></td>
                            <td>
                                <code><?php echo e(Str::limit($apiKey->key, 20)); ?></code>
                            </td>
                            <td>
                                <?php if($apiKey->is_active): ?>
                                <span class="badge badge-success"><?php echo e(t('api_keys.active')); ?></span>
                                <?php elseif($apiKey->is_revoked): ?>
                                <span class="badge badge-danger"><?php echo e(t('api_keys.revoked')); ?></span>
                                <?php elseif($apiKey->is_expired): ?>
                                <span class="badge badge-warning"><?php echo e(t('api_keys.expired')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($apiKey->last_used_at): ?>
                                <?php echo e($apiKey->last_used_at->diffForHumans()); ?>

                                <?php else: ?>
                                <?php echo e(t('api_keys.never')); ?>

                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('admin.api-keys.show', $apiKey)); ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if($apiKey->is_active): ?>
                                <form action="<?php echo e(route('admin.api-keys.revoke', $apiKey)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('<?php echo e(t('api_keys.confirm_revoke')); ?>')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                <?php elseif($apiKey->is_revoked): ?>
                                <form action="<?php echo e(route('admin.api-keys.reactivate', $apiKey)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('<?php echo e(t('api_keys.confirm_reactivate')); ?>')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form action="<?php echo e(route('admin.api-keys.destroy', $apiKey)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo e(t('api_keys.confirm_delete')); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center"><?php echo e(t('api_keys.no_keys')); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($apiKeys->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/api-keys/index.blade.php ENDPATH**/ ?>