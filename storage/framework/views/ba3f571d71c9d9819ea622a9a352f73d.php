<?php $__env->startSection('title', t('admins.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo e(t('admins.title')); ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>"><?php echo e(t('common.dashboard')); ?></a></li>
        <li class="breadcrumb-item active"><?php echo e(t('admins.administrators')); ?></li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-users-cog me-1"></i>
                <?php echo e(t('admins.list')); ?>

            </div>
            <a href="<?php echo e(route('admin.admins.create')); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> <?php echo e(t('admins.new_admin')); ?>

            </a>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="adminsTable">
                    <thead>
                        <tr>
                            <th><?php echo e(t('admins.id')); ?></th>
                            <th><?php echo e(t('common.name')); ?></th>
                            <th><?php echo e(t('common.email')); ?></th>
                            <th><?php echo e(t('admins.role')); ?></th>
                            <th><?php echo e(t('admins.creation_date')); ?></th>
                            <th><?php echo e(t('common.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($admin->id); ?></td>
                                <td><?php echo e($admin->name); ?></td>
                                <td><?php echo e($admin->email); ?></td>
                                <td>
                                    <?php if($admin->role === 'superadmin'): ?>
                                        <span class="badge bg-danger"><?php echo e(t('admins.super_admin')); ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-primary"><?php echo e(t('admins.admin')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($admin->created_at->format('d/m/Y H:i')); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo e(route('admin.admins.edit', $admin->id)); ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($admin->id !== auth()->id()): ?>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal<?php echo e($admin->id); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Modal de confirmation de suppression -->
                                    <div class="modal fade" id="deleteModal<?php echo e($admin->id); ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo e($admin->id); ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel<?php echo e($admin->id); ?>"><?php echo e(t('admins.delete_confirmation')); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php echo e(t('admins.delete_confirm_message', ['name' => $admin->name])); ?>

                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(t('common.cancel')); ?></button>
                                                    <form action="<?php echo e(route('admin.admins.destroy', $admin->id)); ?>" method="POST" style="display: inline;">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-danger"><?php echo e(t('common.delete')); ?></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    $(document).ready(function() {
        $('#adminsTable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[0, 'desc']]
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/admins/index.blade.php ENDPATH**/ ?>