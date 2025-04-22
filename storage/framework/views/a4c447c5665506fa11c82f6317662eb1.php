

<?php $__env->startSection('title', 'Documentation des fournisseurs d\'email'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1><?php echo e(__('Documentation des fournisseurs d\'email')); ?></h1>
                <div class="language-selector">
                    <select class="form-select" onchange="window.location.href = '<?php echo e(route('admin.set.language')); ?>?lang=' + this.value">
                        <?php $__currentLoopData = $availableLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($code); ?>" <?php echo e($currentLanguage === $code ? 'selected' : ''); ?>>
                                <?php echo e($name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="markdown-content">
                        <?php if(!empty($content)): ?>
                            <div id="markdown-content">
                                <?php echo Illuminate\Support\Str::markdown($content); ?>

                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <?php echo e(__('La documentation n\'est pas disponible pour le moment.')); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .markdown-content h1 { font-size: 2rem; margin-bottom: 1rem; }
    .markdown-content h2 { font-size: 1.75rem; margin-top: 2rem; margin-bottom: 1rem; }
    .markdown-content h3 { font-size: 1.5rem; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .markdown-content h4 { font-size: 1.25rem; margin-top: 1.25rem; margin-bottom: 0.5rem; }
    .markdown-content p { margin-bottom: 1rem; }
    .markdown-content ul, .markdown-content ol { margin-bottom: 1rem; padding-left: 2rem; }
    .markdown-content table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; }
    .markdown-content table th, .markdown-content table td { padding: 0.5rem; border: 1px solid #dee2e6; }
    .markdown-content pre { background-color: #f8f9fa; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem; overflow-x: auto; }
    .markdown-content code { background-color: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-size: 0.875em; }
    .markdown-content pre code { padding: 0; background-color: transparent; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/anchor-scroll.js')); ?>"></script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/email-documentation.blade.php ENDPATH**/ ?>