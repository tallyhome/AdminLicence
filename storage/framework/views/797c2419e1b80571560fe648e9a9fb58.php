<?php $__env->startSection('title', t('projects.management')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo e(t('projects.management')); ?></h1>
        <a href="<?php echo e(route('admin.projects.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(t('projects.new_project')); ?>

        </a>
    </div>

    <?php if($projects->isEmpty()): ?>
        <div class="alert alert-info">
            <?php echo e(t('projects.no_projects')); ?>

        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo e(t('common.name')); ?></th>
                        <th><?php echo e(t('common.description')); ?></th>
                        <th><?php echo e(t('projects.license_keys')); ?></th>
                        <th><?php echo e(t('projects.api_keys')); ?></th>
                        <th><?php echo e(t('common.actions')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($project->name); ?></td>
                            <td><?php echo e($project->description ?? t('common.no_description')); ?></td>
                            <td><?php echo e($project->serialKeys->count()); ?></td>
                            <td><?php echo e($project->apiKeys->count()); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo e(route('admin.projects.show', $project)); ?>" class="btn btn-sm btn-info" title="<?php echo e(t('common.view')); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.projects.edit', $project)); ?>" class="btn btn-sm btn-primary" title="<?php echo e(t('common.edit')); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.projects.destroy', $project)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo e(t('projects.confirm_delete')); ?>')" title="<?php echo e(t('common.delete')); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            <?php echo e($projects->links()); ?>

        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/projects/index.blade.php ENDPATH**/ ?>