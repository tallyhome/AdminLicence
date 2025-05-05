<?php $__env->startSection('title', 'Gestion des clés de licence'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des clés de licence</h1>
        <a href="<?php echo e(route('admin.serial-keys.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Créer une clé
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Liste des clés de licence</h3>
        </div>
        <div class="card-body border-bottom pb-3">
            <form action="<?php echo e(route('admin.serial-keys.index')); ?>" method="GET" id="searchForm">
                <input type="hidden" name="per_page" value="<?php echo e(request()->input('per_page', 10)); ?>">
                
                <div class="row g-3 align-items-center">
                    <!-- Recherche générale et sélecteur de pagination -->
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Rechercher une clé, domaine, IP..." name="search" value="<?php echo e(request()->input('search')); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <select class="form-select" style="width: auto; max-width: 140px;" name="per_page" onchange="document.getElementById('searchForm').submit()">
                                <option value="10" <?php echo e(request()->input('per_page', 10) == 10 ? 'selected' : ''); ?>>10 par page</option>
                                <option value="25" <?php echo e(request()->input('per_page') == 25 ? 'selected' : ''); ?>>25 par page</option>
                                <option value="50" <?php echo e(request()->input('per_page') == 50 ? 'selected' : ''); ?>>50 par page</option>
                                <option value="100" <?php echo e(request()->input('per_page') == 100 ? 'selected' : ''); ?>>100 par page</option>
                                <option value="500" <?php echo e(request()->input('per_page') == 500 ? 'selected' : ''); ?>>500 par page</option>
                                <option value="1000" <?php echo e(request()->input('per_page') == 1000 ? 'selected' : ''); ?>>1000 par page</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Filtre par projet -->
                    <div class="col-md-2">
                        <select class="form-select" name="project_id" onchange="document.getElementById('searchForm').submit()">
                            <option value="">Tous les projets</option>
                            <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($project->id); ?>" <?php echo e(request()->input('project_id') == $project->id ? 'selected' : ''); ?>>
                                    <?php echo e($project->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <!-- Filtre par statut -->
                    <div class="col-md-2">
                        <select class="form-select" name="status" onchange="document.getElementById('searchForm').submit()">
                            <option value="">Tous les statuts</option>
                            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php echo e(request()->input('status') == $value ? 'selected' : ''); ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <!-- Filtre par domaine -->
                    <div class="col-md-2">
                        <input type="text" class="form-control" placeholder="Domaine" name="domain" value="<?php echo e(request()->input('domain')); ?>">
                    </div>
                    
                    <!-- Filtre par IP -->
                    <div class="col-md-2">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Adresse IP" name="ip_address" value="<?php echo e(request()->input('ip_address')); ?>">
                            <button class="btn btn-primary" type="submit">Filtrer</button>
                        </div>
                    </div>
                </div>
                
                <?php if(request()->anyFilled(['search', 'project_id', 'domain', 'ip_address', 'status'])): ?>
                    <div class="mt-2">
                        <a href="<?php echo e(route('admin.serial-keys.index')); ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Réinitialiser les filtres
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body pt-0">
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Clé</th>
                            <th>Projet</th>
                            <th>Statut</th>
                            <th>Domaine</th>
                            <th>IP</th>
                            <th>Expiration</th>
                            <th>Actions</th>
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
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif($key->status === 'suspended'): ?>
                                        <span class="badge bg-warning">Suspendue</span>
                                    <?php elseif($key->status === 'revoked'): ?>
                                        <span class="badge bg-danger">Révoquée</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Expirée</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($key->domain ?? 'Non spécifié'); ?></td>
                                <td><?php echo e($key->ip_address ?? 'Non spécifiée'); ?></td>
                                <td><?php echo e($key->expires_at ? $key->expires_at->format('d/m/Y') : 'Sans expiration'); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('admin.serial-keys.show', $key)); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('admin.serial-keys.edit', $key)); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($key->status === 'active'): ?>
                                            <form action="<?php echo e(route('admin.serial-keys.suspend', $key)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir suspendre cette clé ?')">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('admin.serial-keys.revoke', $key)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir révoquer cette clé ?')">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php elseif($key->status === 'suspended'): ?>
                                            <form action="<?php echo e(route('admin.serial-keys.reactivate', $key)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Êtes-vous sûr de vouloir réactiver cette clé ?')">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">Aucune clé de licence trouvée.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination supprimée pour éviter les icônes qui s'affichent en grand -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>Affichage de <?php echo e($serialKeys->firstItem() ?? 0); ?> à <?php echo e($serialKeys->lastItem() ?? 0); ?> sur <?php echo e($serialKeys->total()); ?> clés</div>
                    <div>
                        <?php if($serialKeys->previousPageUrl()): ?>
                            <a href="<?php echo e($serialKeys->previousPageUrl()); ?>" class="btn btn-sm btn-outline-secondary">Précédent</a>
                        <?php endif; ?>
                        
                        <?php if($serialKeys->nextPageUrl()): ?>
                            <a href="<?php echo e($serialKeys->nextPageUrl()); ?>" class="btn btn-sm btn-outline-primary">Suivant</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Script supprimé car nous utilisons maintenant le formulaire pour gérer la pagination
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/serial-keys/index.blade.php ENDPATH**/ ?>