

<?php $__env->startSection('title', 'Gestion des projets'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des projets</h1>
        <a href="<?php echo e(route('admin.projects.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau projet
        </a>
    </div>

    <?php if($projects->isEmpty()): ?>
        <div class="alert alert-info">
            Aucun projet n'a été créé pour le moment.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Clés de licence</th>
                        <th>Clés API</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($project->name); ?></td>
                            <td><?php echo e($project->description ?? 'Aucune description'); ?></td>
                            <td><?php echo e($project->serialKeys->count()); ?></td>
                            <td><?php echo e($project->apiKeys->count()); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo e(route('admin.projects.show', $project)); ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.projects.edit', $project)); ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('admin.projects.destroy', $project)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')">
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