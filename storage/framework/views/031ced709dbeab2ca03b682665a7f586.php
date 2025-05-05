

<?php
use Illuminate\Support\Str;
?>

<?php $__env->startSection('title', __('Gestion des clés API')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('Gestion des clés API')); ?></h1>
        <a href="<?php echo e(route('admin.api-keys.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?php echo e(__('Nouvelle clé API')); ?>

        </a>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.api-keys.index')); ?>" class="row g-3">
                <div class="col-md-4">
                    <label for="project_id" class="form-label"><?php echo e(__('Projet')); ?></label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value=""><?php echo e(__('Tous les projets')); ?></option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($project->id); ?>" <?php echo e(request('project_id') == $project->id ? 'selected' : ''); ?>>
                            <?php echo e($project->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label"><?php echo e(__('Statut')); ?></label>
                    <select name="status" id="status" class="form-select">
                        <option value=""><?php echo e(__('Tous les statuts')); ?></option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>><?php echo e(__('Actives')); ?></option>
                        <option value="revoked" <?php echo e(request('status') == 'revoked' ? 'selected' : ''); ?>><?php echo e(__('Révoquées')); ?></option>
                        <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>><?php echo e(__('Expirées')); ?></option>
                        <option value="used" <?php echo e(request('status') == 'used' ? 'selected' : ''); ?>><?php echo e(__('Utilisées')); ?></option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> <?php echo e(__('Filtrer')); ?>

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
                            <th><?php echo e(__('Nom')); ?></th>
                            <th><?php echo e(__('Projet')); ?></th>
                            <th><?php echo e(__('Clé')); ?></th>
                            <th><?php echo e(__('Statut')); ?></th>
                            <th><?php echo e(__('Dernière utilisation')); ?></th>
                            <th><?php echo e(__('Actions')); ?></th>
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
                                <span class="badge badge-success"><?php echo e(__('Active')); ?></span>
                                <?php elseif($apiKey->is_revoked): ?>
                                <span class="badge badge-danger"><?php echo e(__('Révoquée')); ?></span>
                                <?php elseif($apiKey->is_expired): ?>
                                <span class="badge badge-warning"><?php echo e(__('Expirée')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($apiKey->last_used_at): ?>
                                <?php echo e($apiKey->last_used_at->diffForHumans()); ?>

                                <?php else: ?>
                                <?php echo e(__('Jamais')); ?>

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
                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('<?php echo e(__("Êtes-vous sûr de vouloir révoquer cette clé API ?")); ?>')">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                <?php elseif($apiKey->is_revoked): ?>
                                <form action="<?php echo e(route('admin.api-keys.reactivate', $apiKey)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('<?php echo e(__("Êtes-vous sûr de vouloir réactiver cette clé API ?")); ?>')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form action="<?php echo e(route('admin.api-keys.destroy', $apiKey)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo e(__("Êtes-vous sûr de vouloir supprimer cette clé API ?")); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center"><?php echo e(__('Aucune clé API trouvée.')); ?></td>
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