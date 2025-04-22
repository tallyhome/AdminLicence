<?php $__env->startSection('title', __('Tableau de bord')); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .card-link {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card-link:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .card-link .card {
        transition: border-color 0.2s ease-in-out;
    }
    .card-link:hover .card {
        border-color: #007bff;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">

    <!-- Cartes de statistiques -->
    <div class="row">
        <div class="col-xl col-lg-4 col-md-6 mb-4">
            <a href="<?php echo e(route('admin.serial-keys.index')); ?>" class="text-decoration-none card-link">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    <?php echo e(__('Clés totales')); ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['total_keys']); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-key fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6 mb-4">
            <a href="<?php echo e(route('admin.serial-keys.index', ['status' => 'active'])); ?>" class="text-decoration-none card-link">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    <?php echo e(__('Clés actives')); ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['active_keys']); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-4 col-md-6 mb-4">
            <a href="<?php echo e(route('admin.serial-keys.index', ['used' => 'true'])); ?>" class="text-decoration-none card-link">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    <?php echo e(__('Clés utilisées')); ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['used_keys']); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-laptop fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-6 col-md-6 mb-4">
            <a href="<?php echo e(route('admin.serial-keys.index', ['status' => 'suspended'])); ?>" class="text-decoration-none card-link">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    <?php echo e(__('Clés suspendues')); ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['suspended_keys']); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-pause-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl col-lg-6 col-md-6 mb-4">
            <a href="<?php echo e(route('admin.serial-keys.index', ['status' => 'revoked'])); ?>" class="text-decoration-none card-link">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    <?php echo e(__('Clés révoquées')); ?></div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo e($stats['revoked_keys']); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-ban fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Statistiques des clés par projet -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Utilisation des clés par projet')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Projet')); ?></th>
                                    <th><?php echo e(__('Clés totales')); ?></th>
                                    <th><?php echo e(__('Clés utilisées')); ?></th>
                                    <th><?php echo e(__('Clés disponibles')); ?></th>
                                    <th><?php echo e(__('Statut')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $projectStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($project->name); ?></td>
                                    <td><?php echo e($project->serialKeys_count); ?></td>
                                    <td><?php echo e($project->used_keys_count); ?></td>
                                    <td><?php echo e($project->available_keys_count); ?></td>
                                    <td>
                                        <?php if($project->serialKeys_count > 0): ?>
                                            <div class="progress mb-2" style="height: 20px;">
                                                <?php
                                                    $usedPercentage = ($project->used_keys_count / $project->serialKeys_count) * 100;
                                                    $availablePercentage = 100 - $usedPercentage;
                                                    $progressClass = $project->is_running_low ? 'bg-danger' : 'bg-success';
                                                ?>
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e($usedPercentage); ?>%" 
                                                     aria-valuenow="<?php echo e($usedPercentage); ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo e(round($usedPercentage)); ?>% utilisées
                                                </div>
                                                <div class="progress-bar <?php echo e($progressClass); ?>" role="progressbar" style="width: <?php echo e($availablePercentage); ?>%" 
                                                     aria-valuenow="<?php echo e($availablePercentage); ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo e(round($availablePercentage)); ?>% disponibles
                                                </div>
                                            </div>
                                            <?php if($project->is_running_low): ?>
                                                <span class="badge bg-danger"><?php echo e(__('Stock faible')); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?php echo e(__('Stock suffisant')); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo e(__('Aucune clé')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Utilisation des clés (30 derniers jours)')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Répartition par projet')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <canvas id="projectChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des clés récentes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Clés récentes')); ?></h6>
            <div>
                <select class="form-select" id="perPageSelect" onchange="window.location.href='<?php echo e(route('admin.dashboard')); ?>?per_page=' + this.value">
                    <option value="10" <?php echo e($validPerPage == 10 ? 'selected' : ''); ?>>10 par page</option>
                    <option value="25" <?php echo e($validPerPage == 25 ? 'selected' : ''); ?>>25 par page</option>
                    <option value="50" <?php echo e($validPerPage == 50 ? 'selected' : ''); ?>>50 par page</option>
                    <option value="100" <?php echo e($validPerPage == 100 ? 'selected' : ''); ?>>100 par page</option>
                    <option value="500" <?php echo e($validPerPage == 500 ? 'selected' : ''); ?>>500 par page</option>
                    <option value="1000" <?php echo e($validPerPage == 1000 ? 'selected' : ''); ?>>1000 par page</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Clé')); ?></th>
                            <th><?php echo e(__('Projet')); ?></th>
                            <th><?php echo e(__('Statut')); ?></th>
                            <th><?php echo e(__('Date de création')); ?></th>
                            <th><?php echo e(__('Actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $recentKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key->serial_key); ?></td>
                            <td><?php echo e($key->project->name); ?></td>
                            <td>
                                <span class="badge badge-<?php echo e($key->status == 'active' ? 'success' : ($key->status == 'suspended' ? 'warning' : 'danger')); ?>">
                                    <?php echo e(__(ucfirst($key->status))); ?>

                                </span>
                            </td>
                            <td><?php echo e($key->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <a href="<?php echo e(route('admin.serial-keys.show', $key)); ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination-tailwind">
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique d'utilisation
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    new Chart(usageCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($usageStats->pluck('date')); ?>,
            datasets: [{
                label: '<?php echo e(__("Utilisation")); ?>',
                data: <?php echo json_encode($usageStats->pluck('count')); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Graphique de répartition par projet
    const projectCtx = document.getElementById('projectChart').getContext('2d');
    new Chart(projectCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($projectStats->pluck('name')); ?>,
            datasets: [{
                data: <?php echo json_encode($projectStats->pluck('serial_keys_count')); ?>,
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>