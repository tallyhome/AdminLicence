<?php $__env->startSection('title', 'Gestion de licence'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Gestion de licence</h1>

    <div class="row">
        <div class="col-12">
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="alert-message"><?php echo e(session('success')); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="alert-message"><?php echo e(session('error')); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de licence</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Clé de licence d'installation</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo e($licenseKey ?? 'Non configurée'); ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copyLicenseKey" data-bs-toggle="tooltip" title="Copier la clé">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <h6>Statut de la licence</h6>
                            <div class="mb-3">
                                <?php if($isValid): ?>
                                    <span class="badge bg-success">Valide</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Non valide</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($expiresAt): ?>
                                <h6>Date d'expiration</h6>
                                <div class="mb-3">
                                    <span class="<?php echo e($expiresAt && $expiresAt->isPast() ? 'text-danger' : ''); ?>">
                                        <?php echo e($expiresAt->format('d/m/Y')); ?>

                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <h6>Dernière vérification</h6>
                            <div class="mb-3">
                                <?php echo e($lastCheck ? \Carbon\Carbon::parse($lastCheck)->format('d/m/Y H:i:s') : 'Jamais'); ?>

                            </div>
                            
                            <div class="mt-4">
                                <a href="<?php echo e(route('admin.settings.license.force-check')); ?>" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Vérifier maintenant
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <?php if($licenseDetails): ?>
                                <h6>Détails de la licence</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Projet</th>
                                            <td><?php echo e($licenseDetails->project->name ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Créée le</th>
                                            <td><?php echo e($licenseDetails->created_at->format('d/m/Y')); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Expire le</th>
                                            <td class="<?php echo e($licenseDetails->expires_at && $licenseDetails->expires_at->isPast() ? 'text-danger' : ''); ?>">
                                                <?php echo e($licenseDetails->expires_at ? $licenseDetails->expires_at->format('d/m/Y') : 'Jamais'); ?>

                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Domaine</th>
                                            <td><?php echo e($licenseDetails->domain ?? 'Non défini'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Adresse IP</th>
                                            <td><?php echo e($licenseDetails->ip_address ?? 'Non définie'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Statut</th>
                                            <td>
                                                <?php if($licenseDetails->status == 'active'): ?>
                                                    <span class="badge bg-success">Actif</span>
                                                <?php elseif($licenseDetails->status == 'suspended'): ?>
                                                    <span class="badge bg-warning">Suspendu</span>
                                                <?php elseif($licenseDetails->status == 'revoked'): ?>
                                                    <span class="badge bg-danger">Révoqué</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo e($licenseDetails->status); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Aucune information détaillée disponible pour cette clé de licence.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configuration de la licence</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.settings.license.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="license_key" class="form-label">Clé de licence d'installation</label>
                            <input type="text" class="form-control" id="license_key" name="license_key" value="<?php echo e($licenseKey); ?>" placeholder="XXXX-XXXX-XXXX-XXXX">
                            <div class="form-text">
                                <?php if($envExists): ?>
                                    Cette clé sera enregistrée dans le fichier .env de votre application.
                                <?php else: ?>
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Le fichier .env n'existe pas encore. Il sera créé automatiquement.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="check_frequency" class="form-label">Fréquence de vérification</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="check_frequency" name="check_frequency" value="<?php echo e($checkFrequency); ?>" min="1" max="100" required>
                                <span class="input-group-text">visites</span>
                            </div>
                            <div class="form-text">La licence sera vérifiée une fois tous les N visites du tableau de bord.</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les paramètres
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vérification manuelle</h5>
                </div>
                <div class="card-body">
                    <p>Vous pouvez forcer une vérification immédiate de la licence d'installation. Cela mettra à jour le statut de validité et les informations associées.</p>
                    <a href="<?php echo e(route('admin.settings.license.force-check')); ?>" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Vérifier maintenant
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        
        // Fonction de copie de la clé de licence
        document.getElementById('copyLicenseKey').addEventListener('click', function() {
            var licenseInput = this.parentElement.querySelector('input');
            licenseInput.select();
            document.execCommand('copy');
            
            // Changer temporairement le tooltip
            var tooltip = bootstrap.Tooltip.getInstance(this);
            var originalTitle = this.getAttribute('data-bs-original-title');
            tooltip.hide();
            this.setAttribute('data-bs-original-title', 'Copié !');
            tooltip.show();
            
            // Restaurer le titre original après 1.5 secondes
            setTimeout(function() {
                tooltip.hide();
                this.setAttribute('data-bs-original-title', originalTitle);
            }.bind(this), 1500);
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .license-info-item {
        margin-bottom: 1rem;
    }
    .license-info-item h5 {
        font-size: 0.9rem;
        font-weight: bold;
        color: #4e73df;
        margin-bottom: 0.5rem;
    }
    .license-info-item p {
        margin-bottom: 0.25rem;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/settings/license.blade.php ENDPATH**/ ?>