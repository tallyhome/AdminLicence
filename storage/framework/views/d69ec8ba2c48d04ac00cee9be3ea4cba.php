<?php $__env->startSection('title', 'Outils d\'optimisation'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Outils d'optimisation</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.settings')); ?>">Paramètres</a></li>
        <li class="breadcrumb-item active">Outils d'optimisation</li>
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
    
    <?php if(session('output')): ?>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-terminal me-1"></i>
            Résultat de l'opération
        </div>
        <div class="card-body">
            <pre class="bg-dark text-light p-3" style="max-height: 300px; overflow-y: auto;"><?php echo e(session('output')); ?></pre>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if(session('example')): ?>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-code me-1"></i>
            Exemple de code
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p>Copiez ce code et utilisez-le dans vos vues Blade :</p>
                <code><?php echo e(session('example')); ?></code>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Nettoyage des logs -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-trash me-1"></i>
                    Nettoyage des logs
                </div>
                <div class="card-body">
                    <p>Taille actuelle des logs : <strong><?php echo e($logsSize); ?></strong></p>
                    <p>Cette opération va nettoyer les fichiers de logs inutiles dans le dossier <code>public/install/logs/</code>. Les logs importants seront archivés, les autres seront supprimés.</p>
                    <form action="<?php echo e(route('admin.settings.optimization.clean-logs')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-broom me-1"></i> Nettoyer les logs
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Optimisation des images -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-images me-1"></i>
                    Optimisation des images
                </div>
                <div class="card-body">
                    <p>Taille actuelle des images : <strong><?php echo e($imagesSize); ?></strong></p>
                    <p>Cette opération va optimiser les images dans le dossier <code>public/images/</code> pour réduire leur taille tout en maintenant une qualité acceptable.</p>
                    <form action="<?php echo e(route('admin.settings.optimization.optimize-images')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="quality" class="form-label">Qualité (0-100)</label>
                            <input type="range" class="form-range" min="60" max="95" step="5" id="quality" name="quality" value="80">
                            <div class="d-flex justify-content-between">
                                <span>Compression élevée</span>
                                <span id="qualityValue">80%</span>
                                <span>Haute qualité</span>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="force" name="force">
                            <label class="form-check-label" for="force">Forcer l'optimisation (même si déjà optimisées)</label>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-compress me-1"></i> Optimiser les images
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Outil de diagnostic API -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Outil de diagnostic API
                </div>
                <div class="card-body">
                    <p>L'outil de diagnostic API permet de tester toutes les fonctionnalités API en un seul endroit. Il offre les fonctionnalités suivantes :</p>
                    <ul>
                        <li>Informations générales sur l'API</li>
                        <li>Test de validation des clés de série</li>
                        <li>Test de connexion à l'API externe</li>
                        <li>Test de connexion à la base de données</li>
                        <li>Vérification des permissions des fichiers</li>
                        <li>Affichage des dernières entrées de log</li>
                    </ul>
                    <p><strong>Identifiants par défaut :</strong> admin / AdminLicence2025</p>
                    <a href="<?php echo e(url('/api-diagnostic.php')); ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Ouvrir l'outil de diagnostic API
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Versioning des assets -->
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-code me-1"></i>
                    Versioning des assets
                </div>
                <div class="card-body">
                    <p>Le système de versioning des assets permet d'optimiser la mise en cache des fichiers CSS, JavaScript et images. Il ajoute automatiquement un paramètre de version basé sur la date de modification du fichier.</p>
                    
                    <div class="mb-3">
                        <label for="assetType" class="form-label">Type d'asset</label>
                        <select class="form-select" id="assetType">
                            <option value="css">CSS</option>
                            <option value="js">JavaScript</option>
                            <option value="image">Image</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="cssAssets">
                        <label for="cssPath" class="form-label">Fichier CSS</label>
                        <select class="form-select" id="cssPath">
                            <?php $__currentLoopData = $cssFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($file); ?>"><?php echo e($file); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="jsAssets" style="display: none;">
                        <label for="jsPath" class="form-label">Fichier JavaScript</label>
                        <select class="form-select" id="jsPath">
                            <?php $__currentLoopData = $jsFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($file); ?>"><?php echo e($file); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="imagePath" style="display: none;">
                        <label for="imagePathInput" class="form-label">Chemin de l'image</label>
                        <input type="text" class="form-control" id="imagePathInput" placeholder="images/logo.png">
                    </div>
                    
                    <form action="<?php echo e(route('admin.settings.optimization.asset-example')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="asset_path" id="assetPathHidden">
                        <button type="submit" class="btn btn-primary" id="generateExampleBtn">
                            <i class="fas fa-code me-1"></i> Générer un exemple
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <h5>Comment utiliser</h5>
                        <p>Dans vos fichiers Blade, utilisez les directives suivantes :</p>
                        <ul>
                            <li><code><?php echo \App\Helpers\AssetHelper::css('css/app.css'); ?></code> - Pour les fichiers CSS</li>
                            <li><code><?php echo \App\Helpers\AssetHelper::js('js/app.js'); ?></code> - Pour les fichiers JavaScript</li>
                            <li><code><?php echo \App\Helpers\AssetHelper::image('images/logo.png'); ?></code> - Pour les images</li>
                        </ul>
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
        // Afficher la valeur du slider de qualité
        const qualitySlider = document.getElementById('quality');
        const qualityValue = document.getElementById('qualityValue');
        
        qualitySlider.addEventListener('input', function() {
            qualityValue.textContent = this.value + '%';
        });
        
        // Gestion du changement de type d'asset
        const assetType = document.getElementById('assetType');
        const cssAssets = document.getElementById('cssAssets');
        const jsAssets = document.getElementById('jsAssets');
        const imagePath = document.getElementById('imagePath');
        const assetPathHidden = document.getElementById('assetPathHidden');
        const cssPath = document.getElementById('cssPath');
        const jsPath = document.getElementById('jsPath');
        const imagePathInput = document.getElementById('imagePathInput');
        
        // Initialiser la valeur cachée
        assetPathHidden.value = cssPath.value;
        
        assetType.addEventListener('change', function() {
            cssAssets.style.display = 'none';
            jsAssets.style.display = 'none';
            imagePath.style.display = 'none';
            
            if (this.value === 'css') {
                cssAssets.style.display = 'block';
                assetPathHidden.value = cssPath.value;
            } else if (this.value === 'js') {
                jsAssets.style.display = 'block';
                assetPathHidden.value = jsPath.value;
            } else if (this.value === 'image') {
                imagePath.style.display = 'block';
                assetPathHidden.value = imagePathInput.value;
            }
        });
        
        // Mettre à jour la valeur cachée lors du changement de sélection
        cssPath.addEventListener('change', function() {
            if (assetType.value === 'css') {
                assetPathHidden.value = this.value;
            }
        });
        
        jsPath.addEventListener('change', function() {
            if (assetType.value === 'js') {
                assetPathHidden.value = this.value;
            }
        });
        
        imagePathInput.addEventListener('input', function() {
            if (assetType.value === 'image') {
                assetPathHidden.value = this.value;
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/settings/optimization.blade.php ENDPATH**/ ?>