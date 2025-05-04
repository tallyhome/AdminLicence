

<?php $__env->startSection('title', 'Gestion des tickets de support'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">Tickets de support</h1>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filtres</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.tickets.index')); ?>" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Statut</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Tous</option>
                                <option value="open" <?php echo e(request('status') === 'open' ? 'selected' : ''); ?>>Ouvert</option>
                                <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>>En cours</option>
                                <option value="resolved" <?php echo e(request('status') === 'resolved' ? 'selected' : ''); ?>>Résolu</option>
                                <option value="closed" <?php echo e(request('status') === 'closed' ? 'selected' : ''); ?>>Fermé</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Sujet</th>
                                    <th>Statut</th>
                                    <th>Priorité</th>
                                    <th>Créé le</th>
                                    <th>Dernière réponse</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($ticket->id); ?></td>
                                        <td><?php echo e($ticket->client->name); ?></td>
                                        <td><?php echo e($ticket->subject); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($ticket->status_color); ?>"><?php echo e($ticket->status_label); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo e($ticket->priority_color); ?>"><?php echo e($ticket->priority_label); ?></span>
                                        </td>
                                        <td><?php echo e($ticket->created_at->format('d/m/Y H:i')); ?></td>
                                        <td><?php echo e($ticket->last_reply_at ? $ticket->last_reply_at->format('d/m/Y H:i') : 'Aucune'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Aucun ticket trouvé</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <?php echo e($tickets->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/tickets/index.blade.php ENDPATH**/ ?>