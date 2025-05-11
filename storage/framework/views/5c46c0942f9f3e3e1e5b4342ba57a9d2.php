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
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="fr">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'fr' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-fr me-2"></span> FR
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="en">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'en' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-gb me-2"></span> EN
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="es">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'es' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-es me-2"></span> ES
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="de">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'de' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-de me-2"></span> DE
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="it">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'it' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-it me-2"></span> IT
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="pt">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'pt' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-pt me-2"></span> PT
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="nl">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'nl' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-nl me-2"></span> NL
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="ru">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'ru' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-ru me-2"></span> RU
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="zh">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'zh' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-cn me-2"></span> ZH
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="ja">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'ja' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-jp me-2"></span> JA
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="tr">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'tr' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-tr me-2"></span> TR
                </button>
            </form>
        </li>
        <li>
            <form action="<?php echo e(route('admin.set.language')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="locale" value="ar">
                <button type="submit" class="dropdown-item d-flex align-items-center <?php echo e($locale === 'ar' ? 'active' : ''); ?>">
                    <span class="flag-icon flag-icon-sa me-2"></span> AR
                </button>
            </form>
        </li>
    </ul>
</div> <?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/layouts/partials/language-selector.blade.php ENDPATH**/ ?>