

<?php $__env->startSection('title', 'Détails du projet'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Détails du projet</h1>
        <div>
            <a href="<?php echo e(route('admin.projects.edit', $project)); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="<?php echo e(route('admin.projects.index')); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations du projet</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Nom</th>
                            <td><?php echo e($project->name); ?></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><?php echo e($project->description ?? 'Aucune description'); ?></td>
                        </tr>
                        <tr>
                            <th>URL du site</th>
                            <td>
                                <?php if($project->website_url): ?>
                                    <a href="<?php echo e($project->website_url); ?>" target="_blank"><?php echo e($project->website_url); ?></a>
                                <?php else: ?>
                                    Non spécifiée
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                <span class="badge bg-<?php echo e($project->status === 'active' ? 'success' : 'danger'); ?>">
                                    <?php echo e($project->status === 'active' ? 'Actif' : 'Inactif'); ?>

                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Date de création</th>
                            <td><?php echo e($project->created_at->format('d/m/Y H:i')); ?></td>
                        </tr>
                        <tr>
                            <th>Dernière mise à jour</th>
                            <td><?php echo e($project->updated_at->format('d/m/Y H:i')); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistiques</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Clés de licence</h5>
                        <p class="mb-1">Total : <?php echo e($project->serialKeys->count()); ?></p>
                        <p class="mb-1">Actives : <?php echo e($project->serialKeys->where('status', 'active')->count()); ?></p>
                        <p class="mb-1">Suspendues : <?php echo e($project->serialKeys->where('status', 'suspended')->count()); ?></p>
                        <p class="mb-1">Révoquées : <?php echo e($project->serialKeys->where('status', 'revoked')->count()); ?></p>
                    </div>
                    <div>
                        <h5>Clés API</h5>
                        <p class="mb-1">Total : <?php echo e($project->apiKeys->count()); ?></p>
                        <p class="mb-1">Actives : <?php echo e($project->apiKeys->where('status', 'active')->count()); ?></p>
                        <p class="mb-1">Révoquées : <?php echo e($project->apiKeys->where('status', 'revoked')->count()); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <div class="btn-group">
                        <a href="<?php echo e(route('admin.serial-keys.create', ['project_id' => $project->id])); ?>" class="btn btn-primary">
                            <i class="fas fa-key"></i> Créer une clé de licence
                        </a>
                        <a href="<?php echo e(route('admin.api-keys.create', ['project_id' => $project->id])); ?>" class="btn btn-primary">
                            <i class="fas fa-key"></i> Créer une clé API
                        </a>
                        <form action="<?php echo e(route('admin.projects.destroy', $project)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ? Cette action est irréversible.');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Supprimer le projet
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