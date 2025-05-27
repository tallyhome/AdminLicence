<?php $__env->startSection('title', t('serial_keys.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo e(t('serial_keys.title')); ?></h1>
        <div>
            <a href="<?php echo e(route('admin.license.search')); ?>" class="btn btn-info me-2">
                <i class="fas fa-search"></i> <?php echo e(t('serial_keys.search_by_key')); ?>

            </a>
            <a href="<?php echo e(route('admin.serial-keys.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> <?php echo e(t('serial_keys.create_key')); ?>

            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo e(t('serial_keys.list')); ?></h3>
        </div>
        <div class="card-body border-bottom pb-3">
            <form action="<?php echo e(route('admin.serial-keys.index')); ?>" method="GET" id="searchForm">
                <input type="hidden" name="per_page" value="<?php echo e(request()->input('per_page', 10)); ?>">
                
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status"><?php echo e(t('serial_keys.status')); ?></label>
                            <select name="status" id="status" class="form-control">
                                <option value=""><?php echo e(t('common.all')); ?></option>
                                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_active')); ?></option>
                                <option value="suspended" <?php echo e(request('status') === 'suspended' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_suspended')); ?></option>
                                <option value="revoked" <?php echo e(request('status') === 'revoked' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_revoked')); ?></option>
                                <option value="expired" <?php echo e(request('status') === 'expired' ? 'selected' : ''); ?>><?php echo e(t('serial_keys.status_expired')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group" style="width: 100%; min-width: 120px;">
                            <label for="project"><?php echo e(t('serial_keys.project')); ?></label>
                            <select name="project_id" id="project" class="form-control">
                                <option value=""><?php echo e(t('common.all')); ?></option>
                                <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($project->id); ?>" <?php echo e(request('project_id') == $project->id ? 'selected' : ''); ?>>
                                        <?php echo e($project->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group" style="width: 100%; min-width: 100px;">
                            <label for="used"><?php echo e(t('serial_keys.key_usage')); ?></label>
                            <select name="used" id="used" class="form-control">
                                <option value=""><?php echo e(t('common.all')); ?></option>
                                <option value="true" <?php echo e(request('used') === 'true' ? 'selected' : ''); ?>><?php echo e(t('common.used')); ?></option>
                                <option value="false" <?php echo e(request('used') === 'false' ? 'selected' : ''); ?>><?php echo e(t('common.unused')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group" style="width: 100%; min-width: 60px;">
                            <label for="per_page"><?php echo e(t('pagination.per_page', ['number' => ''])); ?></label>
                            <select name="per_page" id="per_page" class="form-control" onchange="document.getElementById('searchForm').submit();">
                                <option value="10" <?php echo e(request('per_page', 10) == 10 ? 'selected' : ''); ?>>10</option>
                                <option value="25" <?php echo e(request('per_page') == 25 ? 'selected' : ''); ?>>25</option>
                                <option value="50" <?php echo e(request('per_page') == 50 ? 'selected' : ''); ?>>50</option>
                                <option value="100" <?php echo e(request('per_page') == 100 ? 'selected' : ''); ?>>100</option>
                                <option value="250" <?php echo e(request('per_page') == 250 ? 'selected' : ''); ?>>250</option>
                                <option value="500" <?php echo e(request('per_page') == 500 ? 'selected' : ''); ?>>500</option>
                                <option value="1000" <?php echo e(request('per_page') == 1000 ? 'selected' : ''); ?>>1000</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <div class="form-group" style="width: 60%; min-width: 120px;">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> <?php echo e(t('common.search')); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo e(t('serial_keys.key')); ?></th>
                            <th><?php echo e(t('serial_keys.project')); ?></th>
                            <th><?php echo e(t('serial_keys.status')); ?></th>
                            <th><?php echo e(t('serial_keys.domain')); ?></th>
                            <th><?php echo e(t('serial_keys.ip_address')); ?></th>
                            <th><?php echo e(t('serial_keys.expiration')); ?></th>
                            <th><?php echo e(t('serial_keys.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $serialKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <code><?php echo e($key->serial_key); ?></code>
                                </td>
                                <td><?php echo e($key->project->name); ?></td>
                                <td>
                                    <?php if($key->status === 'active'): ?>
                                        <span class="badge bg-success"><?php echo e(t('serial_keys.status_active')); ?></span>
                                    <?php elseif($key->status === 'suspended'): ?>
                                        <span class="badge bg-warning"><?php echo e(t('serial_keys.status_suspended')); ?></span>
                                    <?php elseif($key->status === 'revoked'): ?>
                                        <span class="badge bg-danger"><?php echo e(t('serial_keys.status_revoked')); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?php echo e(t('serial_keys.status_expired')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($key->domain ?? t('serial_keys.not_specified')); ?></td>
                                <td><?php echo e($key->ip_address ?? t('serial_keys.not_specified')); ?></td>
                                <td><?php echo e($key->expires_at ? $key->expires_at->format('d/m/Y') : t('serial_keys.no_expiration')); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('admin.serial-keys.show', $key)); ?>" class="btn btn-sm btn-info" title="<?php echo e(t('serial_keys.view')); ?>">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('admin.serial-keys.edit', $key)); ?>" class="btn btn-sm btn-primary" title="<?php echo e(t('serial_keys.edit')); ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($key->status === 'active'): ?>
                                            <form action="<?php echo e(route('admin.serial-keys.suspend', $key)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('<?php echo e(t('serial_keys.confirm_suspend')); ?>')" title="<?php echo e(t('serial_keys.suspend')); ?>">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('admin.serial-keys.revoke', $key)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo e(t('serial_keys.confirm_revoke')); ?>')" title="<?php echo e(t('serial_keys.revoke')); ?>">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center"><?php echo e(t('serial_keys.no_keys')); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($serialKeys->links('pagination::bootstrap-4')); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/serial-keys/index.blade.php ENDPATH**/ ?>