

<?php $__env->startSection('title', 'Authentification à deux facteurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Authentification à deux facteurs</h1>
        <a href="<?php echo e(route('admin.settings.index')); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour aux paramètres
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Configuration de l'authentification à deux facteurs</h3>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte. 
                        Une fois configurée, vous devrez fournir un code d'authentification généré par votre application 
                        en plus de votre mot de passe pour vous connecter.
                    </p>

                    <?php if($admin->two_factor_enabled): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-shield-alt"></i> L'authentification à deux facteurs est <strong>activée</strong>.
                        </div>

                        <form action="<?php echo e(route('admin.settings.two-factor.disable')); ?>" method="POST" class="mt-3">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="code" class="form-label">Code d'authentification</label>
                                <input type="text" id="code" name="code" class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       required maxlength="6" placeholder="123456">
                                <?php $__errorArgs = ['code'];
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
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times-circle"></i> Désactiver l'authentification à deux facteurs
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> L'authentification à deux facteurs est <strong>désactivée</strong>.
                        </div>

                        <div class="mt-3">
                            <p class="mb-2">1. Scannez le code QR avec votre application d'authentification :</p>
                            <div class="text-center p-3 bg-light rounded mb-3">
                                <img src="<?php echo e($qrCodeUrl); ?>" 
                                     alt="QR Code" class="img-fluid">
                            </div>
                            
                            <p class="mb-2">2. Ou entrez manuellement cette clé secrète dans votre application :</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" value="<?php echo e($secret); ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copySecret()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            
                            <p class="mb-2">3. Entrez le code généré par votre application pour vérifier :</p>
                            <form action="<?php echo e(route('admin.settings.two-factor.enable')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code d'authentification</label>
                                    <input type="text" id="code" name="code" class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           required maxlength="6" placeholder="123456">
                                    <?php $__errorArgs = ['code'];
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
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-shield-alt"></i> Activer l'authentification à deux facteurs
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <?php if($admin->two_factor_enabled): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Codes de récupération</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">
                            Les codes de récupération vous permettent de vous connecter à votre compte si vous n'avez pas accès 
                            à votre application d'authentification. <strong>Conservez-les dans un endroit sûr</strong>, car ils ne seront 
                            affichés qu'une seule fois.
                        </p>

                        <?php if(session('recoveryCodes')): ?>
                            <div class="alert alert-warning">
                                <p><strong>Conservez ces codes de récupération dans un endroit sûr :</strong></p>
                                <ul class="list-group mb-3">
                                    <?php $__currentLoopData = session('recoveryCodes'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="list-group-item"><?php echo e($code); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <p class="mb-0"><small>Chaque code ne peut être utilisé qu'une seule fois.</small></p>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo e(route('admin.settings.two-factor.regenerate-recovery-codes')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-sync"></i> Régénérer les codes de récupération
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if(!$admin->two_factor_enabled): ?>
<script>
function copySecret() {
    const secretInput = document.querySelector('input[value="<?php echo e($secret); ?>"]');
    secretInput.select();
    document.execCommand('copy');
    alert('Clé secrète copiée dans le presse-papiers');
}
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/settings/two-factor.blade.php ENDPATH**/ ?>