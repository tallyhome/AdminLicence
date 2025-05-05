

<?php $__env->startSection('title', __('Informations de version')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Version actuelle')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="mb-4"><?php echo e($version['full']); ?></h2>
                            <p><strong><?php echo e(__('Dernière mise à jour')); ?>:</strong> <?php echo e($version['last_update']); ?></p>
                            <p>
                                <span class="badge bg-primary">Major: <?php echo e($version['major']); ?></span>
                                <span class="badge bg-secondary">Minor: <?php echo e($version['minor']); ?></span>
                                <span class="badge bg-info">Patch: <?php echo e($version['patch']); ?></span>
                                <?php if($version['release']): ?>
                                    <span class="badge bg-warning"><?php echo e($version['release']); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h5><?php echo e(__('À propos des numéros de version')); ?></h5>
                                <ul class="mb-0">
                                    <li><strong>Major</strong> - Changements majeurs/incompatibles</li>
                                    <li><strong>Minor</strong> - Nouvelles fonctionnalités compatibles</li>
                                    <li><strong>Patch</strong> - Corrections de bugs compatibles</li>
                                    <li><strong>Release</strong> - Suffixe de version (alpha, beta, rc, etc.)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><?php echo e(__('Historique des versions')); ?></h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="timeline-item mb-5">
                                <div class="row">
                                    <div class="col-md-3">
                                        <h5><?php echo e($item['version']); ?></h5>
                                        <p class="text-muted"><?php echo e($item['date']); ?></p>
                                    </div>
                                    <div class="col-md-9">
                                        <p><strong><?php echo e($item['description']); ?></strong></p>
                                        <?php if(isset($item['categories'])): ?>
                                            <?php if(isset($item['categories']['Ajouts']) && count($item['categories']['Ajouts']) > 0): ?>
                                                <h6 class="mt-3 text-success">Ajouts</h6>
                                                <ul>
                                                    <?php $__currentLoopData = $item['categories']['Ajouts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><?php echo e($change); ?></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php endif; ?>
                                            
                                            <?php if(isset($item['categories']['Améliorations']) && count($item['categories']['Améliorations']) > 0): ?>
                                                <h6 class="mt-3 text-primary">Améliorations</h6>
                                                <ul>
                                                    <?php $__currentLoopData = $item['categories']['Améliorations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><?php echo e($change); ?></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php endif; ?>
                                            
                                            <?php if(isset($item['categories']['Corrections de bugs']) && count($item['categories']['Corrections de bugs']) > 0): ?>
                                                <h6 class="mt-3 text-danger">Corrections de bugs</h6>
                                                <ul>
                                                    <?php $__currentLoopData = $item['categories']['Corrections de bugs']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><?php echo e($change); ?></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/version/index.blade.php ENDPATH**/ ?>