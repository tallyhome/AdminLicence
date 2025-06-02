<?php $__env->startSection('title', t('projects.show.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo e(t('projects.show.title')); ?></h1>
        <div class="btn-group">
            <a href="<?php echo e(route('admin.projects.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?php echo e(t('common.back')); ?>

            </a>
            <a href="<?php echo e(route('admin.projects.edit', $project)); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> <?php echo e(t('common.edit')); ?>

            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('projects.show.details')); ?></h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4"><?php echo e(t('projects.show.name')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($project->name); ?></dd>

                        <dt class="col-sm-4"><?php echo e(t('projects.show.description')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($project->description ?? t('common.no_description')); ?></dd>

                        <dt class="col-sm-4"><?php echo e(t('projects.show.website_url')); ?></dt>
                        <dd class="col-sm-8">
                            <?php if($project->website_url): ?>
                                <a href="<?php echo e($project->website_url); ?>" target="_blank"><?php echo e($project->website_url); ?></a>
                            <?php else: ?>
                                <?php echo e(t('common.not_specified')); ?>

                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4"><?php echo e(t('projects.show.status')); ?></dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-<?php echo e($project->status === 'active' ? 'success' : 'danger'); ?>">
                                <?php echo e(t('projects.show.status_' . $project->status)); ?>

                            </span>
                        </dd>

                        <dt class="col-sm-4"><?php echo e(t('projects.show.created_at')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($project->created_at->format('d/m/Y H:i')); ?></dd>

                        <dt class="col-sm-4"><?php echo e(t('projects.show.updated_at')); ?></dt>
                        <dd class="col-sm-8"><?php echo e($project->updated_at->format('d/m/Y H:i')); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('projects.show.statistics')); ?></h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6"><?php echo e(t('projects.show.total_license_keys')); ?></dt>
                        <dd class="col-sm-6"><?php echo e($project->serialKeys->count()); ?></dd>

                        <dt class="col-sm-6"><?php echo e(t('projects.show.total_api_keys')); ?></dt>
                        <dd class="col-sm-6"><?php echo e($project->apiKeys->count()); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('projects.show.actions')); ?></h3>
                </div>
                <div class="card-body">
                    <div class="btn-group">
                        <a href="<?php echo e(route('admin.serial-keys.create', ['project_id' => $project->id])); ?>" class="btn btn-primary">
                            <i class="fas fa-key"></i> <?php echo e(t('projects.show.create_license_key')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.api-keys.create', ['project_id' => $project->id])); ?>" class="btn btn-primary">
                            <i class="fas fa-key"></i> <?php echo e(t('projects.show.create_api_key')); ?>

                        </a>
                        <form action="<?php echo e(route('admin.projects.destroy', $project)); ?>" method="POST" class="d-inline" onsubmit="return confirm('<?php echo e(t('projects.show.confirm_delete')); ?>');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> <?php echo e(t('projects.show.delete_project')); ?>

                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/projects/show.blade.php ENDPATH**/ ?>