<?php $__env->startSection('title', t('settings.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo e(t('settings.title')); ?></h1>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?php echo e(t('common.back')); ?>

        </a>
    </div>


    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('settings.general.title')); ?></h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="mb-3">
                            <label for="site_name" class="form-label"><?php echo e(t('settings.general.site_name')); ?></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['site_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="site_name" name="site_name" 
                                   value="<?php echo e(old('site_name', $settings['site_name'])); ?>" required>
                            <?php $__errorArgs = ['site_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="site_description" class="form-label"><?php echo e(t('settings.general.site_description')); ?></label>
                            <textarea class="form-control <?php $__errorArgs = ['site_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="site_description" name="site_description" 
                                      rows="3"><?php echo e(old('site_description', $settings['site_description'])); ?></textarea>
                            <?php $__errorArgs = ['site_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="contact_email" class="form-label"><?php echo e(t('settings.general.contact_email')); ?></label>
                            <input type="email" class="form-control <?php $__errorArgs = ['contact_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="contact_email" name="contact_email" 
                                   value="<?php echo e(old('contact_email', $settings['contact_email'] ?? '')); ?>" required>
                            <?php $__errorArgs = ['contact_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="contact_name" class="form-label"><?php echo e(t('settings.general.contact_name')); ?></label>
                            <input type="text" class="form-control <?php $__errorArgs = ['contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="contact_name" name="contact_name" 
                                   value="<?php echo e(old('contact_name', $settings['contact_name'] ?? '')); ?>" required>
                            <?php $__errorArgs = ['contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo e(t('common.save')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">


        <!-- Changer le mot de passe -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('settings.password.title')); ?></h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.settings.update-password')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label"><?php echo e(t('settings.password.current_password')); ?></label>
                            <input type="password" id="current_password" name="current_password" class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo e(t('settings.password.new_password')); ?></label>
                            <input type="password" id="password" name="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label"><?php echo e(t('settings.password.confirm_password')); ?></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i> <?php echo e(t('settings.password.update_button')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Favicon -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('settings.favicon.title')); ?></h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p><?php echo e(t('settings.favicon.current')); ?> :</p>
                        <img src="<?php echo e(asset('favicon.ico')); ?>" alt="Favicon" class="img-thumbnail" style="max-width: 64px;">
                    </div>

                    <form action="<?php echo e(route('admin.settings.update-favicon')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="mb-3">
                            <label for="favicon" class="form-label"><?php echo e(t('settings.favicon.new')); ?></label>
                            <input type="file" id="favicon" name="favicon" class="form-control <?php $__errorArgs = ['favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <div class="form-text"><?php echo e(t('settings.favicon.accepted_formats')); ?></div>
                            <?php $__errorArgs = ['favicon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> <?php echo e(t('settings.favicon.update_button')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Thème -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('settings.theme.title')); ?></h3>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.settings.toggle-dark-mode')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="dark_mode" name="dark_mode" <?php echo e($darkModeEnabled ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="dark_mode"><?php echo e(t('settings.theme.dark_mode')); ?></label>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-palette"></i> <?php echo e(t('settings.theme.apply_button')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Gestion de licence -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestion de licence</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Gérez la licence d'installation de votre application AdminLicence et configurez les paramètres de vérification périodique.
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <a href="<?php echo e(route('admin.settings.license')); ?>" class="btn btn-primary">
                            <i class="fas fa-key"></i> Gérer la licence
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Authentification à deux facteurs -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo e(t('settings.two_factor.title')); ?></h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <?php echo e(t('settings.two_factor.description')); ?>

                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <?php if(auth()->guard('admin')->user()->two_factor_enabled): ?>
                                <span class="badge bg-success"><?php echo e(t('settings.two_factor.status.enabled')); ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning"><?php echo e(t('settings.two_factor.status.disabled')); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo e(route('admin.settings.two-factor')); ?>" class="btn btn-primary me-2">
                            <i class="fas fa-shield-alt"></i> <?php echo e(t('settings.two_factor.configure')); ?>

                        </a>
                        <a href="<?php echo e(route('admin.settings.test-google2fa')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-vial"></i> <?php echo e(t('settings.two_factor.test')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Outils d'optimisation -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Outils d'optimisation</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Optimisez les performances de votre application avec des outils pour nettoyer les logs, optimiser les images et gérer les assets.
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <a href="<?php echo e(route('admin.settings.optimization')); ?>" class="btn btn-primary">
                            <i class="fas fa-tools"></i> Accéder aux outils d'optimisation
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Diagnostic API -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Diagnostic API</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        Testez et diagnostiquez les fonctionnalités API de votre application : validation de clés, connexion à la base de données, permissions et logs.
                    </p>
                    
                    <div class="d-flex align-items-center mb-3">
                        <a href="<?php echo e(route('admin.settings.api-diagnostic')); ?>" class="btn btn-primary me-2">
                            <i class="fas fa-stethoscope"></i> Interface de diagnostic
                        </a>
                        <a href="<?php echo e(url('/api-diagnostic.php')); ?>" target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-external-link-alt"></i> Outil autonome
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/settings/index.blade.php ENDPATH**/ ?>