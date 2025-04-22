

<?php $__env->startSection('title', __('Détails de la clé API')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo e(__('Détails de la clé API')); ?></h1>
        <div>
            <?php if($apiKey->is_active): ?>
            <form action="<?php echo e(route('admin.api-keys.revoke', $apiKey)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <button type="submit" class="btn btn-warning" onclick="return confirm('<?php echo e(__("Êtes-vous sûr de vouloir révoquer cette clé API ?")); ?>')">
                    <i class="fas fa-ban"></i> <?php echo e(__('Révoquer')); ?>

                </button>
            </form>
            <?php elseif($apiKey->is_revoked): ?>
            <form action="<?php echo e(route('admin.api-keys.reactivate', $apiKey)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <button type="submit" class="btn btn-success" onclick="return confirm('<?php echo e(__("Êtes-vous sûr de vouloir réactiver cette clé API ?")); ?>')">
                    <i class="fas fa-check"></i> <?php echo e(__('Réactiver')); ?>

                </button>
            </form>
            <?php endif; ?>
            <form action="<?php echo e(route('admin.api-keys.destroy', $apiKey)); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-danger" onclick="return confirm('<?php echo e(__("Êtes-vous sûr de vouloir supprimer cette clé API ?")); ?>')">
                    <i class="fas fa-trash"></i> <?php echo e(__('Supprimer')); ?>

                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Informations de base -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Informations de base')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <tr>
                                <th width="30%"><?php echo e(__('Nom')); ?></th>
                                <td><?php echo e($apiKey->name); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Projet')); ?></th>
                                <td><?php echo e($apiKey->project->name); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Clé')); ?></th>
                                <td>
                                    <code><?php echo e($apiKey->key); ?></code>
                                    <button class="btn btn-sm btn-outline-secondary copy-key" data-clipboard-text="<?php echo e($apiKey->key); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Secret')); ?></th>
                                <td>
                                    <code><?php echo e($apiKey->secret); ?></code>
                                    <button class="btn btn-sm btn-outline-secondary copy-secret" data-clipboard-text="<?php echo e($apiKey->secret); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Statut')); ?></th>
                                <td>
                                    <?php if($apiKey->is_active): ?>
                                    <span class="badge badge-success"><?php echo e(__('Active')); ?></span>
                                    <?php elseif($apiKey->is_revoked): ?>
                                    <span class="badge badge-danger"><?php echo e(__('Révoquée')); ?></span>
                                    <?php elseif($apiKey->is_expired): ?>
                                    <span class="badge badge-warning"><?php echo e(__('Expirée')); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Date d\'expiration')); ?></th>
                                <td><?php echo e($apiKey->expires_at ? $apiKey->expires_at->format('d/m/Y H:i') : __('Aucune')); ?></td>
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
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Statistiques d\'utilisation')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <tr>
                                <th width="30%"><?php echo e(__('Total des utilisations')); ?></th>
                                <td><?php echo e($stats['total_usage']); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Dernière utilisation')); ?></th>
                                <td><?php echo e($stats['last_used'] ? $stats['last_used']->diffForHumans() : __('Jamais')); ?></td>
                            </tr>
                            <tr>
                                <th><?php echo e(__('Date de création')); ?></th>
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
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Permissions')); ?></h6>
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
                                <?php echo e(__($description)); ?>

                            </label>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo e(__('Enregistrer les permissions')); ?>

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