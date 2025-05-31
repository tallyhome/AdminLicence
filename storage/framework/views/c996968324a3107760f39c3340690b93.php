<?php $__env->startSection('title', 'Diagnostic API'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .card.cursor-pointer:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
        transition: all 0.2s ease-in-out;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Diagnostic API</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.settings.index')); ?>">Paramètres</a></li>
        <li class="breadcrumb-item active">Diagnostic API</li>
    </ol>
    
    <?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
    <div class="alert alert-danger">
        <?php echo e(session('error')); ?>

    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-xl-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-tools me-1"></i>
                        Outil de diagnostic API
                    </div>
                    <a href="<?php echo e($apiDiagnosticUrl); ?>" target="_blank" class="btn btn-sm btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Ouvrir dans une nouvelle fenêtre
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Informations d'accès</h5>
                        <p>L'outil de diagnostic API est accessible à l'URL suivante : <code><?php echo e($apiDiagnosticUrl); ?></code></p>
                        <p><strong>Identifiants par défaut :</strong> <code>admin</code> / <code>AdminLicence2025</code></p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Fonctionnalités disponibles</h5>
                            <ul class="list-group mb-4">
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    <div>
                                        <strong>Informations générales</strong>
                                        <p class="mb-0 text-muted small">Vue d'ensemble de l'API et de la configuration du serveur</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-key me-2 text-primary"></i>
                                    <div>
                                        <strong>Test de clé de série</strong>
                                        <p class="mb-0 text-muted small">Vérifiez la validité d'une clé de licence</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-network-wired me-2 text-primary"></i>
                                    <div>
                                        <strong>Test de connexion</strong>
                                        <p class="mb-0 text-muted small">Vérifiez la connectivité avec l'API externe</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-database me-2 text-primary"></i>
                                    <div>
                                        <strong>Test de base de données</strong>
                                        <p class="mb-0 text-muted small">Vérifiez la connexion à la base de données</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-lock me-2 text-primary"></i>
                                    <div>
                                        <strong>Vérification des permissions</strong>
                                        <p class="mb-0 text-muted small">Contrôlez les permissions des fichiers critiques</p>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex align-items-center">
                                    <i class="fas fa-file-alt me-2 text-primary"></i>
                                    <div>
                                        <strong>Affichage des logs</strong>
                                        <p class="mb-0 text-muted small">Consultez les dernières entrées de log</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="embed-responsive" style="height: 500px; border: 1px solid #ddd; border-radius: 4px;">
                                <iframe src="<?php echo e($apiDiagnosticUrl); ?>" style="width: 100%; height: 100%; border: none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="row">
        <!-- Test de clé de série -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-key me-1"></i>
                    Test de clé de série
                </div>
                <div class="card-body">
                    <form id="serialKeyForm">
                        <div class="mb-3">
                            <label for="serialKey" class="form-label">Clé de série</label>
                            <select class="form-select" id="serialKey" name="serial_key">
                                <option value="">-- Sélectionnez une clé --</option>
                                <?php $__currentLoopData = $serialKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key->serial_key); ?>"><?php echo e($key->serial_key); ?> (<?php echo e($key->project->name ?? 'Aucun projet'); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <option value="custom">Saisir une clé personnalisée</option>
                            </select>
                        </div>
                        <div class="mb-3" id="customKeyField" style="display: none;">
                            <label for="customKey" class="form-label">Clé personnalisée</label>
                            <input type="text" class="form-control" id="customKey" placeholder="XXXX-XXXX-XXXX-XXXX">
                        </div>
                        <div class="mb-3">
                            <label for="domain" class="form-label">Domaine (optionnel)</label>
                            <input type="text" class="form-control" id="domain" name="domain" placeholder="exemple.com">
                        </div>
                        <div class="mb-3">
                            <label for="ipAddress" class="form-label">Adresse IP (optionnel)</label>
                            <input type="text" class="form-control" id="ipAddress" name="ip_address" placeholder="192.168.1.1">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle me-1"></i> Tester la clé
                        </button>
                    </form>
                    
                    <div id="serialKeyResult" class="mt-4" style="display: none;">
                        <h5>Résultat</h5>
                        <div id="serialKeyResultContent" class="p-3 border rounded"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informations sur le serveur -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-server me-1"></i>
                    Informations sur le serveur
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Version PHP</th>
                                <td><?php echo e($serverInfo['php_version']); ?></td>
                            </tr>
                            <tr>
                                <th>Version Laravel</th>
                                <td><?php echo e($serverInfo['laravel_version']); ?></td>
                            </tr>
                            <tr>
                                <th>Serveur Web</th>
                                <td><?php echo e($serverInfo['server_software']); ?></td>
                            </tr>
                            <tr>
                                <th>Système d'exploitation</th>
                                <td><?php echo e($serverInfo['os']); ?></td>
                            </tr>
                            <tr>
                                <th>Base de données</th>
                                <td><?php echo e($serverInfo['database']); ?></td>
                            </tr>
                            <tr>
                                <th>Fuseau horaire</th>
                                <td><?php echo e($serverInfo['timezone']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h5 class="mt-4">Extensions PHP</h5>
                    <div class="row">
                        <?php $__currentLoopData = $serverInfo['extensions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $extension => $loaded): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-4 mb-2">
                            <span class="badge <?php echo e($loaded ? 'bg-success' : 'bg-danger'); ?>">
                                <i class="fas <?php echo e($loaded ? 'fa-check' : 'fa-times'); ?> me-1"></i>
                                <?php echo e($extension); ?>

                            </span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <div class="mt-4">
                        <form action="<?php echo e(route('admin.settings.api-diagnostic.test-api-connection')); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-network-wired me-1"></i> Tester la connexion API
                            </button>
                        </form>
                        
                        <form action="<?php echo e(route('admin.settings.api-diagnostic.test-database')); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-database me-1"></i> Tester la base de données
                            </button>
                        </form>
                        
                        <form action="<?php echo e(route('admin.settings.api-diagnostic.check-permissions')); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-lock me-1"></i> Vérifier les permissions
                            </button>
                        </form>
                    </div>
                    
                    <?php if(session('test_result')): ?>
                    <div class="mt-4">
                        <h5><?php echo e(session('test_result_title')); ?></h5>
                        <div class="p-3 border rounded">
                            <?php echo session('test_result'); ?>

                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Statistiques de la base de données -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Statistiques de la base de données
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <a href="<?php echo e(route('admin.serial-keys.index')); ?>" class="text-decoration-none">
                                <div class="card bg-primary text-white h-100" id="serialKeysCard" style="cursor: pointer;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small">Clés de série</div>
                                                <div class="text-lg fw-bold"><?php echo e($dbStats['serial_keys'] ?? 0); ?></div>
                                            </div>
                                            <i class="fas fa-key fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-primary border-0 text-center py-1">
                                        <span class="small text-white-75">
                                            <i class="fas fa-external-link-alt"></i> Voir toutes les clés
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="<?php echo e(route('admin.projects.index')); ?>" class="text-decoration-none">
                                <div class="card bg-success text-white h-100" id="projectsCard" style="cursor: pointer;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small">Projets</div>
                                                <div class="text-lg fw-bold"><?php echo e($dbStats['projects'] ?? 0); ?></div>
                                            </div>
                                            <i class="fas fa-project-diagram fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-success border-0 text-center py-1">
                                        <span class="small text-white-75">
                                            <i class="fas fa-external-link-alt"></i> Voir tous les projets
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="<?php echo e(route('admin.admins.index')); ?>" class="text-decoration-none">
                                <div class="card bg-warning text-white h-100" id="adminsCard" style="cursor: pointer;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small">Administrateurs</div>
                                                <div class="text-lg fw-bold"><?php echo e($dbStats['admins'] ?? 0); ?></div>
                                            </div>
                                            <i class="fas fa-users-cog fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-warning border-0 text-center py-1">
                                        <span class="small text-white-75">
                                            <i class="fas fa-external-link-alt"></i> Voir tous les administrateurs
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="<?php echo e(route('admin.serial-keys.index', ['status' => 'active'])); ?>" class="text-decoration-none">
                                <div class="card bg-info text-white h-100" id="activeKeysCard" style="cursor: pointer;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small">Clés actives</div>
                                                <div class="text-lg fw-bold"><?php echo e($dbStats['active_keys'] ?? 0); ?></div>
                                            </div>
                                            <i class="fas fa-check-circle fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-info border-0 text-center py-1">
                                        <span class="small text-white-75">
                                            <i class="fas fa-external-link-alt"></i> Voir les clés actives
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6 mb-4">
                            <a href="<?php echo e(route('admin.api-keys.index')); ?>" class="text-decoration-none">
                                <div class="card bg-secondary text-white h-100" id="apiKeysCard" style="cursor: pointer;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="me-3">
                                                <div class="text-white-75 small">Clés API</div>
                                                <div class="text-lg fw-bold"><?php echo e($dbStats['api_keys'] ?? 0); ?></div>
                                            </div>
                                            <i class="fas fa-key fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-secondary border-0 text-center py-1">
                                        <span class="small text-white-75">
                                            <i class="fas fa-external-link-alt"></i> Voir toutes les clés API
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Derniers logs -->
        <div class="col-xl-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-file-alt me-1"></i>
                        Derniers logs
                    </div>
                    <form action="<?php echo e(route('admin.settings.api-diagnostic.get-logs')); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i> Rafraîchir
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                        <div id="logsContent">
                            <?php if(count($logEntries) > 0): ?>
                                <?php $__currentLoopData = $logEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="log-entry mb-2">
                                    <span class="text-muted small">[<?php echo e($entry['timestamp']); ?>]</span>
                                    <pre class="mb-0 mt-1" style="white-space: pre-wrap; font-size: 0.8rem;"><?php echo e($entry['content']); ?></pre>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <p class="text-muted">Aucune entrée de log disponible</p>
                            <?php endif; ?>
                        </div>
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
        // Fonctions pour charger les données dans les modales
        function loadSerialKeys() {
            const modalBody = document.querySelector('#serialKeysModal .modal-body');
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Chargement des clés de série...</p></div>';
            
            fetch('<?php echo e(route("admin.settings.api-diagnostic.get-serial-keys")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Clé</th><th>Projet</th><th>Statut</th><th>Date d\'expiration</th></tr></thead><tbody>';
                    
                    data.items.forEach(item => {
                        html += `<tr>
                            <td>${item.serial_key}</td>
                            <td>${item.project_name || 'N/A'}</td>
                            <td><span class="badge bg-${item.status_class}">${item.status}</span></td>
                            <td>${item.expires_at || 'Aucune'}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-info">Aucune clé de série trouvée.</div>';
                }
            })
            .catch(error => {
                modalBody.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des clés de série.</div>';
                console.error('Error:', error);
            });
        }
        
        function loadProjects() {
            const modalBody = document.querySelector('#projectsModal .modal-body');
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Chargement des projets...</p></div>';
            
            fetch('<?php echo e(route("admin.settings.api-diagnostic.get-projects")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Nom</th><th>Statut</th><th>Clés de série</th><th>Date de création</th></tr></thead><tbody>';
                    
                    data.items.forEach(item => {
                        html += `<tr>
                            <td>${item.name}</td>
                            <td><span class="badge bg-${item.status_class}">${item.status}</span></td>
                            <td>${item.serial_keys_count}</td>
                            <td>${item.created_at}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-info">Aucun projet trouvé.</div>';
                }
            })
            .catch(error => {
                modalBody.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des projets.</div>';
                console.error('Error:', error);
            });
        }
        
        function loadAdmins() {
            const modalBody = document.querySelector('#adminsModal .modal-body');
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Chargement des administrateurs...</p></div>';
            
            fetch('<?php echo e(route("admin.settings.api-diagnostic.get-admins")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Nom</th><th>Email</th><th>Dernière connexion</th></tr></thead><tbody>';
                    
                    data.items.forEach(item => {
                        html += `<tr>
                            <td>${item.name}</td>
                            <td>${item.email}</td>
                            <td>${item.last_login || 'Jamais'}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-info">Aucun administrateur trouvé.</div>';
                }
            })
            .catch(error => {
                modalBody.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des administrateurs.</div>';
                console.error('Error:', error);
            });
        }
        
        function loadActiveKeys() {
            const modalBody = document.querySelector('#activeKeysModal .modal-body');
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Chargement des clés actives...</p></div>';
            
            fetch('<?php echo e(route("admin.settings.api-diagnostic.get-active-keys")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Clé</th><th>Projet</th><th>Domaine</th><th>Date d\'expiration</th></tr></thead><tbody>';
                    
                    data.items.forEach(item => {
                        html += `<tr>
                            <td>${item.serial_key}</td>
                            <td>${item.project_name || 'N/A'}</td>
                            <td>${item.domain || 'Tous'}</td>
                            <td>${item.expires_at || 'Aucune'}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-info">Aucune clé active trouvée.</div>';
                }
            })
            .catch(error => {
                modalBody.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des clés actives.</div>';
                console.error('Error:', error);
            });
        }
        
        function loadApiKeys() {
            const modalBody = document.querySelector('#apiKeysModal .modal-body');
            modalBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Chargement des clés API...</p></div>';
            
            fetch('<?php echo e(route("admin.settings.api-diagnostic.get-api-keys")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-striped table-sm"><thead><tr><th>Clé</th><th>Projet</th><th>Statut</th><th>Dernière utilisation</th></tr></thead><tbody>';
                    
                    data.items.forEach(item => {
                        html += `<tr>
                            <td>${item.key.substring(0, 10)}...</td>
                            <td>${item.project_name || 'N/A'}</td>
                            <td><span class="badge bg-${item.status_class}">${item.status}</span></td>
                            <td>${item.last_used_at || 'Jamais'}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-info">Aucune clé API trouvée.</div>';
                }
            })
            .catch(error => {
                modalBody.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des clés API.</div>';
                console.error('Error:', error);
            });
        }
        
        // Initialisation des événements pour les modales
        document.querySelector('#serialKeysModal').addEventListener('show.bs.modal', loadSerialKeys);
        document.querySelector('#projectsModal').addEventListener('show.bs.modal', loadProjects);
        document.querySelector('#adminsModal').addEventListener('show.bs.modal', loadAdmins);
        document.querySelector('#activeKeysModal').addEventListener('show.bs.modal', loadActiveKeys);
        document.querySelector('#apiKeysModal').addEventListener('show.bs.modal', loadApiKeys);
        
        // Gestionnaire d'événements pour les cartes statistiques cliquables
        document.addEventListener('DOMContentLoaded', function() {
            // Sélectionner tous les éléments avec la classe modal-trigger
            var triggers = document.querySelectorAll('.modal-trigger');
            
            // Ajouter un gestionnaire d'événements click à chaque élément
            triggers.forEach(function(trigger) {
                trigger.style.cursor = 'pointer'; // Assurer que le curseur est un pointeur
                
                trigger.addEventListener('click', function() {
                    var modalId = this.getAttribute('data-modal-target');
                    console.log('Ouverture de la modale:', modalId);
                    
                    try {
                        var modalElement = document.getElementById(modalId);
                        if (!modalElement) {
                            console.error('Erreur: Modale non trouvée:', modalId);
                            return;
                        }
                        
                        // Utiliser l'API Bootstrap pour ouvrir la modale
                        var modal = new bootstrap.Modal(modalElement);
                        modal.show();
                    } catch (e) {
                        console.error('Erreur lors de l\'ouverture de la modale:', e);
                    }
                });
            });
        });
        // Gestion du champ de clé personnalisée
        const serialKeySelect = document.getElementById('serialKey');
        const customKeyField = document.getElementById('customKeyField');
        const customKeyInput = document.getElementById('customKey');
        
        serialKeySelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customKeyField.style.display = 'block';
            } else {
                customKeyField.style.display = 'none';
            }
        });
        
        // Test de clé de série
        const serialKeyForm = document.getElementById('serialKeyForm');
        const serialKeyResult = document.getElementById('serialKeyResult');
        const serialKeyResultContent = document.getElementById('serialKeyResultContent');
        
        serialKeyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const serialKey = serialKeySelect.value === 'custom' ? customKeyInput.value : serialKeySelect.value;
            const domain = document.getElementById('domain').value;
            const ipAddress = document.getElementById('ipAddress').value;
            
            if (!serialKey) {
                alert('Veuillez sélectionner ou saisir une clé de série');
                return;
            }
            
            // Afficher un indicateur de chargement
            serialKeyResult.style.display = 'block';
            serialKeyResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Vérification en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('<?php echo e(route('admin.settings.api-diagnostic.test-serial-key')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    serial_key: serialKey,
                    domain: domain,
                    ip_address: ipAddress
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const result = data.result;
                    let statusClass = result.valid ? 'success' : 'danger';
                    let statusText = result.valid ? 'Valide' : 'Invalide';
                    
                    let html = `
                        <div class="alert alert-${statusClass}">
                            <strong>Statut : ${statusText}</strong><br>
                            Message : ${result.message}
                        </div>
                        <div class="mt-3">
                            <h6>Détails</h6>
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Projet</th>
                                        <td>${result.project || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Date d'expiration</th>
                                        <td>${result.expires_at || 'Aucune'}</td>
                                    </tr>
                                    <tr>
                                        <th>Statut</th>
                                        <td>${result.status || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Token</th>
                                        <td><code>${result.token || 'N/A'}</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    serialKeyResultContent.innerHTML = html;
                } else {
                    serialKeyResultContent.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Erreur</strong><br>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                serialKeyResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Test de connexion API
        const testApiConnectionBtn = document.getElementById('testApiConnectionBtn');
        const testResult = document.getElementById('testResult');
        const testResultTitle = document.getElementById('testResultTitle');
        const testResultContent = document.getElementById('testResultContent');
        
        testApiConnectionBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            testResult.style.display = 'block';
            testResultTitle.textContent = 'Test de connexion API';
            testResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Test en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('<?php echo e(route('admin.settings.api-diagnostic.test-api-connection')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                let statusClass = data.success ? 'success' : 'danger';
                let statusText = data.success ? 'Succès' : 'Échec';
                
                let html = `
                    <div class="alert alert-${statusClass}">
                        <strong>Statut : ${statusText}</strong><br>
                        Code HTTP : ${data.status_code || 'N/A'}
                    </div>
                `;
                
                if (data.response) {
                    html += `
                        <div class="mt-3">
                            <h6>Réponse</h6>
                            <pre class="bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto;">${JSON.stringify(data.response, null, 2)}</pre>
                        </div>
                    `;
                }
                
                testResultContent.innerHTML = html;
            })
            .catch(error => {
                testResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Test de base de données
        const testDatabaseBtn = document.getElementById('testDatabaseBtn');
        
        testDatabaseBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            testResult.style.display = 'block';
            testResultTitle.textContent = 'Test de connexion à la base de données';
            testResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Test en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('<?php echo e(route('admin.settings.api-diagnostic.test-database')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                let statusClass = data.success ? 'success' : 'danger';
                let statusText = data.success ? 'Succès' : 'Échec';
                
                let html = `
                    <div class="alert alert-${statusClass}">
                        <strong>Statut : ${statusText}</strong><br>
                        ${data.message}
                    </div>
                `;
                
                if (data.success) {
                    html += `
                        <div class="mt-3">
                            <h6>Informations</h6>
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Driver</th>
                                        <td>${data.driver}</td>
                                    </tr>
                                    <tr>
                                        <th>Base de données</th>
                                        <td>${data.database}</td>
                                    </tr>
                                    <tr>
                                        <th>Version</th>
                                        <td>${data.version}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                testResultContent.innerHTML = html;
            })
            .catch(error => {
                testResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Vérification des permissions
        const checkPermissionsBtn = document.getElementById('checkPermissionsBtn');
        
        checkPermissionsBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            testResult.style.display = 'block';
            testResultTitle.textContent = 'Vérification des permissions';
            testResultContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Vérification en cours...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('<?php echo e(route('admin.settings.api-diagnostic.check-permissions')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                let statusClass = data.success ? 'success' : 'warning';
                let statusText = data.success ? 'OK' : 'Attention';
                
                let html = `
                    <div class="alert alert-${statusClass}">
                        <strong>Statut : ${statusText}</strong><br>
                        ${data.message}
                    </div>
                `;
                
                if (data.permissions) {
                    html += `
                        <div class="mt-3">
                            <h6>Permissions des fichiers</h6>
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fichier/Dossier</th>
                                        <th>Existe</th>
                                        <th>Permissions</th>
                                        <th>Accès en écriture</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    data.permissions.forEach(item => {
                        const existsClass = item.exists ? 'success' : 'danger';
                        const writableClass = item.writable ? 'success' : (item.should_be_writable ? 'danger' : 'warning');
                        
                        html += `
                            <tr>
                                <td>${item.name}</td>
                                <td><span class="badge bg-${existsClass}">${item.exists ? 'Oui' : 'Non'}</span></td>
                                <td>${item.permissions}</td>
                                <td><span class="badge bg-${writableClass}">${item.writable ? 'Oui' : 'Non'}</span></td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                testResultContent.innerHTML = html;
            })
            .catch(error => {
                testResultContent.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Erreur</strong><br>
                        Une erreur s'est produite lors de la communication avec le serveur.
                    </div>
                `;
                console.error('Error:', error);
            });
        });
        
        // Gestion des clics sur les cartes de statistiques
        document.querySelector('.card[data-bs-target="#serialKeysModal"]').addEventListener('click', function() {
            loadModalData('<?php echo e(route("admin.settings.api-diagnostic.get-serial-keys")); ?>', '#serialKeysModal', function(data) {
                let tableHtml = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Clé de série</th>
                                <th>Projet</th>
                                <th>Statut</th>
                                <th>Expiration</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.items.forEach(item => {
                    tableHtml += `
                        <tr>
                            <td><code>${item.serial_key}</code></td>
                            <td>${item.project_name || 'N/A'}</td>
                            <td><span class="badge bg-${item.status_class}">${item.status}</span></td>
                            <td>${item.expires_at || 'N/A'}</td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                        </tbody>
                    </table>
                `;
                
                return tableHtml;
            });
        });
        
        document.querySelector('.card[data-bs-target="#projectsModal"]').addEventListener('click', function() {
            loadModalData('<?php echo e(route("admin.settings.api-diagnostic.get-projects")); ?>', '#projectsModal', function(data) {
                let tableHtml = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Statut</th>
                                <th>Clés</th>
                                <th>Créé le</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.items.forEach(item => {
                    tableHtml += `
                        <tr>
                            <td>${item.name}</td>
                            <td><span class="badge bg-${item.status_class}">${item.status}</span></td>
                            <td>${item.serial_keys_count}</td>
                            <td>${item.created_at}</td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                        </tbody>
                    </table>
                `;
                
                return tableHtml;
            });
        });
        
        document.querySelector('.card[data-bs-target="#adminsModal"]').addEventListener('click', function() {
            loadModalData('<?php echo e(route("admin.settings.api-diagnostic.get-admins")); ?>', '#adminsModal', function(data) {
                let tableHtml = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Dernière connexion</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.items.forEach(item => {
                    tableHtml += `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.email}</td>
                            <td>${item.last_login || 'Jamais'}</td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                        </tbody>
                    </table>
                `;
                
                return tableHtml;
            });
        });
        
        document.querySelector('.card[data-bs-target="#activeKeysModal"]').addEventListener('click', function() {
            loadModalData('<?php echo e(route("admin.settings.api-diagnostic.get-active-keys")); ?>', '#activeKeysModal', function(data) {
                let tableHtml = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Clé de série</th>
                                <th>Projet</th>
                                <th>Domaine</th>
                                <th>Expiration</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.items.forEach(item => {
                    tableHtml += `
                        <tr>
                            <td><code>${item.serial_key}</code></td>
                            <td>${item.project_name || 'N/A'}</td>
                            <td>${item.domain || 'N/A'}</td>
                            <td>${item.expires_at || 'N/A'}</td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                        </tbody>
                    </table>
                `;
                
                return tableHtml;
            });
        });
        
        document.querySelector('.card[data-bs-target="#apiKeysModal"]').addEventListener('click', function() {
            loadModalData('<?php echo e(route("admin.settings.api-diagnostic.get-api-keys")); ?>', '#apiKeysModal', function(data) {
                let tableHtml = `
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Clé API</th>
                                <th>Projet</th>
                                <th>Statut</th>
                                <th>Dernière utilisation</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.items.forEach(item => {
                    tableHtml += `
                        <tr>
                            <td><code>${item.key}</code></td>
                            <td>${item.project_name || 'N/A'}</td>
                            <td><span class="badge bg-${item.status_class}">${item.status}</span></td>
                            <td>${item.last_used_at || 'Jamais'}</td>
                        </tr>
                    `;
                });
                
                tableHtml += `
                        </tbody>
                    </table>
                `;
                
                return tableHtml;
            });
        });
        
        // Fonction utilitaire pour charger les données dans une modale
        function loadModalData(url, modalSelector, renderCallback) {
            const modalBody = document.querySelector(`${modalSelector} .modal-body`);
            
            // Afficher le spinner de chargement
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Chargement des données...</p>
                </div>
            `;
            
            // Effectuer la requête AJAX
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items && data.items.length > 0) {
                    modalBody.innerHTML = renderCallback(data);
                } else {
                    modalBody.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Aucune donnée disponible
                        </div>
                    `;
                }
            })
            .catch(error => {
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i> Erreur lors du chargement des données
                    </div>
                `;
                console.error('Erreur:', error);
            });
        }
    
        // Rafraîchir les logs
        const refreshLogsBtn = document.getElementById('refreshLogsBtn');
        const logsContent = document.getElementById('logsContent');
        
        refreshLogsBtn.addEventListener('click', function() {
            // Afficher un indicateur de chargement
            logsContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Chargement des logs...</p></div>';
            
            // Envoyer la requête AJAX
            fetch('<?php echo e(route('admin.settings.api-diagnostic.get-logs')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.logs.length > 0) {
                    let html = '';
                    
                    data.logs.forEach(entry => {
                        html += `
                            <div class="log-entry mb-2">
                                <span class="text-muted small">[${entry.timestamp}]</span>
                                <pre class="mb-0 mt-1" style="white-space: pre-wrap; font-size: 0.8rem;">${entry.content}</pre>
                            </div>
                        `;
                    });
                    
                    logsContent.innerHTML = html;
                } else {
                    logsContent.innerHTML = '<p class="text-muted">Aucune entrée de log disponible</p>';
                }
            })
            .catch(error => {
                logsContent.innerHTML = '<p class="text-danger">Erreur lors du chargement des logs</p>';
                console.error('Error:', error);
            });
        });
    });
</script>

<!-- Inclusion des modales -->
<?php echo $__env->make('admin.settings.api-diagnostic-modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sélectionner toutes les cartes cliquables
        const clickableCards = document.querySelectorAll('.card-clickable');
        
        // Ajouter un gestionnaire d'événements à chaque carte
        clickableCards.forEach(function(card) {
            card.addEventListener('click', function() {
                // Récupérer l'ID de la modale à ouvrir
                const modalId = this.getAttribute('data-modal-id');
                
                // Ouvrir la modale en utilisant l'API JavaScript native de Bootstrap
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            });
        });
    });
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/settings/api-diagnostic.blade.php ENDPATH**/ ?>