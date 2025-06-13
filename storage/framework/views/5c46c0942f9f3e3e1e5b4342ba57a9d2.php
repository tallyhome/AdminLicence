<div class="dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 70px; cursor: pointer;">
        <i class="flag-icon flag-icon-<?php echo e(app()->getLocale() === 'en' ? 'gb' : app()->getLocale()); ?> me-1"></i>
        <span class="d-none d-md-inline"><?php echo e(strtoupper(app()->getLocale())); ?></span>
    </a>
    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
        <?php $__currentLoopData = config('app.available_locales'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li>
                <form action="<?php echo e(route('admin.set.language')); ?>" method="POST" class="language-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="locale" value="<?php echo e($locale); ?>">
                    <button type="submit" class="dropdown-item d-flex align-items-center">
                        <i class="flag-icon flag-icon-<?php echo e($locale === 'en' ? 'gb' : $locale); ?> me-2"></i>
                        <?php echo e(strtoupper($locale)); ?>

                    </button>
                </form>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Assurer que le dropdown de langue fonctionne correctement
        const languageDropdown = document.getElementById('languageDropdown');
        if (languageDropdown) {
            // Utiliser l'API Bootstrap pour initialiser le dropdown
            const dropdownInstance = new bootstrap.Dropdown(languageDropdown);
            
            // Ajouter un gestionnaire d'événements pour le clic
            languageDropdown.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Ajout de stopPropagation pour éviter les conflits
            });
        }
        
        // Assurer que les formulaires de langue fonctionnent correctement
        document.querySelectorAll('.language-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // Laisser le formulaire se soumettre normalement
                console.log('Formulaire de langue soumis pour: ' + form.querySelector('input[name="locale"]').value);
            });
        });
    });
</script><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/layouts/partials/language-selector.blade.php ENDPATH**/ ?>