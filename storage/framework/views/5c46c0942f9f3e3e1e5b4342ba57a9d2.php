<?php
use Illuminate\Support\Facades\Session;
?>

<!-- Language Selector -->
<div class="nav-item dropdown language-selector">
    <button class="nav-link dropdown-toggle d-flex align-items-center" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 70px;">
        <?php
            $locale = Session::get('locale', app()->getLocale());
            $countryCode = $locale === 'en' ? 'gb' : $locale;
        ?>
        <span class="flag-icon flag-icon-<?php echo e($countryCode); ?> me-2"></span>
        <?php echo e(strtoupper($locale)); ?>

    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown" style="min-width: 100px;">
        <?php $__currentLoopData = config('app.available_locales', ['fr', 'en', 'es', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja', 'tr', 'ar']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST" class="block">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="<?php echo e($locale); ?>">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e(app()->getLocale() == $locale ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-<?php echo e($locale === 'en' ? 'gb' : $locale); ?> me-2"></span>
                    <?php echo e(strtoupper($locale)); ?>

                </button>
            </form>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div> <?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/layouts/partials/language-selector.blade.php ENDPATH**/ ?>