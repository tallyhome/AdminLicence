<?php $__env->startSection('title', t('settings_license.license.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid p-0">
    <h1 class="h3 mb-3"><?php echo e(t('settings_license.license.title')); ?></h1>

    <div class="row">
        <div class="col-12">
            <!-- Les messages d'alerte sont gérés par le layout principal -->

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(t('settings_license.license.info_title')); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><?php echo e(t('settings_license.license.installation_key')); ?></h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo e($licenseKey ?? 'Non configurée'); ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copyLicenseKey" data-bs-toggle="tooltip" title="<?php echo e(t('settings_license.license.copy_key')); ?>">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <h6><?php echo e(t('settings_license.license.status')); ?></h6>
                            <div class="mb-3">
                                <?php if($isValid): ?>
                                    <span class="badge bg-success"><?php echo e(t('settings_license.license.valid')); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><?php echo e(t('settings_license.license.invalid')); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($expiresAt): ?>
                                <h6><?php echo e(t('settings_license.license.expiry_date')); ?></h6>
                                <div class="mb-3">
                                    <span class="<?php echo e($expiresAt && $expiresAt->isPast() ? 'text-danger' : ''); ?>">
                                        <?php echo e($expiresAt->format('d/m/Y')); ?>

                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <h6><?php echo e(t('settings_license.license.last_check')); ?></h6>
                            <div class="mb-3">
                                <?php echo e($lastCheck ? \Carbon\Carbon::parse($lastCheck)->format('d/m/Y H:i:s') : t('settings_license.license.never')); ?>

                            </div>
                            
                            <div class="mt-4">
                                <a href="<?php echo e(route('admin.settings.license.force-check')); ?>" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> <?php echo e(t('settings_license.license.check_now')); ?>

                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <?php if(session('license_details') || $licenseDetails): ?>
                                <h6><?php echo e(t('settings_license.license.details')); ?></h6>
                                <table class="table table-sm">
                                    <tbody>
                                        <?php if(session('license_details')): ?>
                                        <tr>
                                            <th><?php echo e(t('settings_license.license.status_label')); ?></th>
                                            <td>
                                                <?php
                                                    $status = session('license_details')['status'] ?? 'inconnu';
                                                    $statusClass = '';
                                                    $statusText = $status;
                                                    
                                                    switch($status) {
                                                        case 'active':
                                                            $statusClass = 'text-success';
                                                            $statusText = 'Active';
                                                            break;
                                                        case 'suspended':
                                                            $statusClass = 'text-warning';
                                                            $statusText = 'Suspendue';
                                                            break;
                                                        case 'revoked':
                                                            $statusClass = 'text-danger';
                                                            $statusText = 'Révoquée';
                                                            break;
                                                        default:
                                                            $statusText = ucfirst($status);
                                                    }
                                                ?>
                                                <span class="<?php echo e($statusClass); ?>"><?php echo e($statusText); ?></span>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <th><?php echo e(t('settings_license.license.expiry_date_label')); ?></th>
                                            <td>
                                                <?php
                                                    $expiryDate = session('license_details')['expiry_date'] ?? null;
                                                    $hasExpiry = false;
                                                    $expiry = null;
                                                    $expired = false;
                                                    
                                                    if (!empty($expiryDate)) {
                                                        try {
                                                            $expiry = new \DateTime($expiryDate);
                                                            $now = new \DateTime();
                                                            $expired = $expiry < $now;
                                                            $hasExpiry = true;
                                                        } catch (\Exception $e) {
                                                            // Si la date n'est pas au bon format, on l'affiche telle quelle
                                                            $hasExpiry = false;
                                                        }
                                                    }
                                                ?>
                                                
                                                <?php if($hasExpiry): ?>
                                                    <span class="<?php echo e($expired ? 'text-danger' : 'text-success'); ?>">
                                                        <?php echo e($expiry->format('d/m/Y')); ?>

                                                        <?php if($expired): ?>
                                                            <i class="fas fa-exclamation-triangle" data-bs-toggle="tooltip" title="Licence expirée"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-check-circle" data-bs-toggle="tooltip" title="Licence valide jusqu'à cette date"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <?php if($expiryDate): ?>
                                                        <?php echo e($expiryDate); ?> <i class="fas fa-info-circle" data-bs-toggle="tooltip" title="Format de date non reconnu"></i>
                                                    <?php else: ?>
                                                        <span class="text-muted">Non spécifiée</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        
                                        <?php if(session('license_details')['registered_domain']): ?>
                                        <tr>
                                            <th>Domaine enregistré</th>
                                            <td><?php echo e(session('license_details')['registered_domain']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        
                                        <?php if(session('license_details')['registered_ip']): ?>
                                        <tr>
                                            <th>Adresse IP enregistrée</th>
                                            <td><?php echo e(session('license_details')['registered_ip']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php if($licenseDetails): ?>
                                        <tr>
                                            <th>Projet</th>
                                            <td><?php echo e($licenseDetails->project->name ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Créée le</th>
                                            <td><?php echo e($licenseDetails->created_at->format('d/m/Y')); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo e(t('settings_license.license.expires_on')); ?></th>
                                            <td class="<?php echo e($licenseDetails->expires_at && $licenseDetails->expires_at->isPast() ? 'text-danger' : ''); ?>">
                                                <?php echo e($licenseDetails->expires_at ? $licenseDetails->expires_at->format('d/m/Y') : t('settings_license.license.never')); ?>

                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php echo e(t('settings_license.license.domain')); ?></th>
                                            <td><?php echo e($licenseDetails->domain ?? t('settings_license.license.not_defined')); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo e(t('settings_license.license.ip_address')); ?></th>
                                            <td><?php echo e($licenseDetails->ip_address ?? t('settings_license.license.not_defined')); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?php echo e(t('settings_license.license.status')); ?></th>
                                            <td>
                                                <?php if($licenseDetails->status == 'active'): ?>
                                                     <span class="badge bg-success"><?php echo e(t('settings_license.license.status_active')); ?></span>
                                                <?php elseif($licenseDetails->status == 'suspended'): ?>
                                                     <span class="badge bg-warning"><?php echo e(t('settings_license.license.status_suspended')); ?></span>
                                                <?php elseif($licenseDetails->status == 'revoked'): ?>
                                                     <span class="badge bg-danger"><?php echo e(t('settings_license.license.status_revoked')); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo e($licenseDetails->status); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <?php echo e(t('settings_license.license.no_details')); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(t('settings_license.license.configuration')); ?></h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.settings.license.update')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="license_key" class="form-label"><?php echo e(t('settings_license.license.installation_key')); ?></label>
                            <input type="text" class="form-control" id="license_key" name="license_key" value="<?php echo e($licenseKey); ?>" placeholder="XXXX-XXXX-XXXX-XXXX">
                            <div class="form-text">
                                <?php if($envExists): ?>
                                    <?php echo e(t('settings_license.license.key_saved_in_env')); ?>

                                <?php else: ?>
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> <?php echo e(t('settings_license.license.env_not_exists')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        

                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?php echo e(t('settings_license.license.save_settings')); ?>

                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo e(t('settings_license.license.manual_verification')); ?></h5>
                </div>
                <div class="card-body">
                    <p><?php echo e(t('settings_license.license.manual_verification_desc')); ?></p>
                    <a href="<?php echo e(route('admin.settings.license.force-check')); ?>" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> <?php echo e(t('settings_license.license.check_now')); ?>

                    </a>
                </div>
            </div>
            
            <!-- Débogage -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?php echo e(t('settings_license.license.debug_info')); ?></h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="card-body collapse" id="debugInfo">
                    <h6><?php echo e(t('settings_license.license.expiry_date')); ?></h6>
                    <div class="mb-3">
                        <?php echo e(t('settings_license.license.detected_value')); ?>: <code><?php echo e((string) \App\Models\Setting::get('debug_expiry_date', t('settings_license.license.not_found'))); ?></code>
                    </div>
                    
                    <h6><?php echo e(t('settings_license.license.status')); ?></h6>
                    <div class="mb-3">
                        <?php echo e(t('settings_license.license.detected_value')); ?>: <code><?php echo e((string) \App\Models\Setting::get('license_status', t('settings_license.license.not_found'))); ?></code>
                    </div>
                    
                    <h6><?php echo e(t('settings_license.license.http_code')); ?></h6>
                    <div class="mb-3">
                        <code><?php echo e((string) \App\Models\Setting::get('debug_api_http_code', 'N/A')); ?></code>
                    </div>
                    
                    <h6><?php echo e(t('settings_license.license.raw_api_response')); ?></h6>
                    <div class="mb-3">
                        <?php
                            $apiResponse = \App\Models\Setting::get('debug_api_response', t('settings_license.license.no_response'));
                            if (!is_string($apiResponse)) {
                                $apiResponse = json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: t('settings_license.license.unviewable_format');
                            }
                        ?>
                        <textarea class="form-control" rows="8" readonly><?php echo e($apiResponse); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss pour les alertes de succès après 5 secondes
        const successAlerts = document.querySelectorAll('.alert-success');
        if (successAlerts.length > 0) {
            setTimeout(function() {
                successAlerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000); // 5000ms = 5 secondes
        }
        
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
            this.setAttribute('data-bs-original-title', '<?php echo e(t('settings_license.license.copied')); ?>');
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