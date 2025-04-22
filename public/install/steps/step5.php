<div class="step-content">
    <h2><?php echo $translations['installation_summary']; ?></h2>
    
    <div id="install-result" class="alert d-none mt-3"></div>
    
    <!-- Conteneur pour les détails de connexion -->
    <div id="connection-details" class="d-none mt-3"></div>
    
    <div class="installation-progress">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo $translations['license_check']; ?></h5>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <p><?php echo $translations['license_valid']; ?> <?php echo isset($_SESSION['license_expiry']) ? '- ' . $translations['expiry_date'] . ': ' . $_SESSION['license_expiry'] : ''; ?></p>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo $translations['project_detection_check']; ?></h5>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <?php
                $projectType = '';
                switch($_SESSION['project_type'] ?? 'php') {
                    case 'php':
                        $projectType = $translations['php_project'];
                        break;
                    case 'laravel':
                        $projectType = $translations['laravel_project'];
                        break;
                    case 'react':
                        $projectType = $translations['react_project'];
                        break;
                }
                ?>
                <p><?php echo $projectType; ?></p>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo $translations['env_creation_check']; ?></h5>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <?php if ($_SESSION['project_type'] === 'php'): ?>
                    <p><?php echo $translations['env_not_needed']; ?></p>
                <?php else: ?>
                    <p><?php echo isset($_SESSION['has_env']) && $_SESSION['has_env'] ? $translations['env_file_exists'] : $translations['env_file_missing']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo $translations['vendor_check']; ?></h5>
                <div class="progress mb-2">
                    <div class="progress-bar <?php echo ($_SESSION['project_type'] === 'php' || (isset($_SESSION['has_vendor']) && $_SESSION['has_vendor'])) ? 'bg-success' : 'bg-warning'; ?>" role="progressbar" style="width: 100%"></div>
                </div>
                <?php if ($_SESSION['project_type'] === 'php'): ?>
                    <p><?php echo $translations['vendor_not_needed']; ?></p>
                <?php else: ?>
                    <p><?php echo isset($_SESSION['has_vendor']) && $_SESSION['has_vendor'] ? $translations['vendor_exists'] : $translations['vendor_missing'] . ' - ' . $translations['run_composer']; ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo $translations['database_check']; ?></h5>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <p><?php echo $translations['connection_success']; ?></p>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo $translations['admin_setup_check']; ?></h5>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 100%"></div>
                </div>
                <p><?php echo $translations['admin_saved']; ?></p>
            </div>
        </div>
    </div>
    
    <div id="installation-progress-bar" class="progress mb-4 d-none">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
    </div>
    
    <!-- Logs d'installation en temps réel -->
    <div id="installation-logs" class="card mb-4 d-none">
        <div class="card-header">
            <?php echo $translations['installation_logs'] ?? 'Installation Logs'; ?>
        </div>
        <div class="card-body">
            <div id="log-container" class="bg-dark text-light p-3" style="max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.9rem;">
                <div id="log-content"></div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between mt-4">
        <a href="?step=4" class="btn btn-secondary"><?php echo $translations['return']; ?></a>
        <div>
            <button type="button" id="start-installation" class="btn btn-success"><?php echo $translations['start_installation']; ?></button>
            <a href="../" id="go-to-login" class="btn btn-primary d-none"><?php echo $translations['go_to_login']; ?></a>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Fonction pour ajouter un message aux logs
    function addLogMessage(message, type) {
        var logClass = '';
        switch(type) {
            case 'success':
                logClass = 'text-success';
                break;
            case 'error':
                logClass = 'text-danger';
                break;
            case 'warning':
                logClass = 'text-warning';
                break;
            case 'info':
            default:
                logClass = 'text-info';
        }
        
        var timestamp = new Date().toLocaleTimeString();
        var logLine = $('<div class="log-line"></div>').html(
            '<span class="text-secondary">[' + timestamp + ']</span> ' +
            '<span class="' + logClass + '">' + message + '</span>'
        );
        
        $('#log-content').append(logLine);
        
        // Auto-scroll to bottom
        var logContainer = document.getElementById('log-container');
        logContainer.scrollTop = logContainer.scrollHeight;
    }
    
    // Fonction pour mettre à jour la progression
    function updateProgress(percent) {
        $('#installation-progress-bar .progress-bar').css('width', percent + '%');
    }
    
    $('#start-installation').on('click', function() {
        // Show progress bar and logs
        $('#installation-progress-bar').removeClass('d-none');
        $('#installation-logs').removeClass('d-none');
        
        // Reset logs
        $('#log-content').empty();
        
        // Disable button
        var installingText = "<?php echo addslashes($translations['installing']); ?>";
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + installingText + '...');
        
        // Clear previous result
        $('#install-result').removeClass('alert-success alert-danger').addClass('d-none');
        
        // Initial log message
        addLogMessage("<?php echo addslashes($translations['starting_installation'] ?? 'Starting installation process...'); ?>", 'info');
        
        // Start installation avec le script qui utilise directement Artisan pour créer toutes les tables
        $.ajax({
            url: 'ajax/install_artisan.php',
            type: 'POST',
            dataType: 'json',
            timeout: 60000, // Augmenter le timeout à 60 secondes
            success: function(response) {
                // Afficher la réponse complète pour le débogage
                console.log('Réponse complète:', response);
                
                // Vérifier si la réponse est valide
                if (!response) {
                    // Afficher un message d'erreur
                    $('#install-result').removeClass('d-none').addClass('alert-danger')
                        .html("Erreur: La réponse du serveur est invalide. Vérifiez les logs d'installation.");
                    
                    // Réactiver le bouton
                    $('#start-installation').prop('disabled', false).html('<?php echo addslashes($translations["start_installation"]); ?>');
                    return;
                }
                
                // Traiter la réponse
                if (response.status) {
                    // Installation réussie
                    $('#install-result').removeClass('d-none alert-danger').addClass('alert-success')
                        .html(response.message);
                    
                    // Process each step
                    var totalSteps = response.steps ? response.steps.length : 0;
                    var completedSteps = 0;
                    
                    // Log project type
                    var projectTypeText = response.project_type === 'laravel' ? 
                        "<?php echo addslashes($translations['laravel_project']); ?>" : 
                        "<?php echo addslashes($translations['php_project']); ?>";
                    addLogMessage("<?php echo addslashes($translations['detected_project_type'] ?? 'Detected project type:'); ?> " + projectTypeText, 'info');
                    
                    // Process each installation step
                    if (response.steps && Array.isArray(response.steps)) {
                        $.each(response.steps, function(index, step) {
                            completedSteps++;
                            var percentComplete = Math.round((completedSteps / totalSteps) * 100);
                            updateProgress(percentComplete);
                            
                            // Log step result
                            var stepType = 'info';
                            if (step.status) {
                                stepType = 'success';
                            } else {
                                stepType = 'error';
                            }
                            
                            // Format step name for display
                            var stepName = '';
                            switch(step.step) {
                                case 'env_creation':
                                    stepName = "<?php echo addslashes($translations['env_creation_check']); ?>";
                                    break;
                                case 'vendor_installation':
                                    stepName = "<?php echo addslashes($translations['vendor_installation'] ?? 'Vendor Installation'); ?>";
                                    break;
                                case 'admin_creation':
                                    stepName = "<?php echo addslashes($translations['admin_setup_check']); ?>";
                                    break;
                                case 'migrations':
                                    stepName = "<?php echo addslashes($translations['database_migrations'] ?? 'Database Migrations'); ?>";
                                    break;
                                case 'seeders':
                                    stepName = "<?php echo addslashes($translations['database_seeders'] ?? 'Database Seeders'); ?>";
                                    break;
                                case 'installation_completed':
                                    stepName = "<?php echo addslashes($translations['installation_completed'] ?? 'Installation Completed'); ?>";
                                    break;
                                default:
                                    stepName = step.step.replace(/_/g, ' ');
                            }
                            
                            addLogMessage(stepName + ': ' + step.message, stepType);
                            
                            // If there's output, show it
                            if (step.output && Array.isArray(step.output) && step.output.length > 0) {
                                var outputDiv = $('<div class="log-output mt-1 mb-2 pl-3 text-muted" style="border-left: 2px solid #6c757d; font-size: 0.85rem;"></div>');
                                $.each(step.output.slice(0, 10), function(i, line) {
                                    outputDiv.append($('<div></div>').text(line));
                                });
                                
                                if (step.output.length > 10) {
                                    outputDiv.append($('<div></div>').text('... ' + (step.output.length - 10) + ' more lines'));
                                }
                                
                                $('#log-content').append(outputDiv);
                            }
                        });
                    }
                    
                    // Afficher les détails de connexion
                    $.ajax({
                        url: 'ajax/get_admin_details.php',
                        type: 'GET',
                        dataType: 'json',
                        success: function(adminData) {
                            if (adminData.status) {
                                var detailsHtml = '<div class="mt-4 p-3 border rounded bg-light">';
                                detailsHtml += '<h4><?php echo addslashes($translations['connection_details'] ?? "Détails de connexion"); ?></h4>';
                                detailsHtml += '<div class="row">';
                                
                                // SuperAdmin
                                detailsHtml += '<div class="col-md-4">';
                                detailsHtml += '<div class="card mb-3">';
                                detailsHtml += '<div class="card-header bg-primary text-white">SuperAdmin</div>';
                                detailsHtml += '<div class="card-body">';
                                detailsHtml += '<p><strong>Email:</strong> ' + adminData.email + '</p>';
                                detailsHtml += '<p><strong>Mot de passe:</strong> ' + adminData.password_hint + '</p>';
                                detailsHtml += '<p><a href="' + adminData.admin_url + '/login" target="_blank" class="btn btn-sm btn-primary">Se connecter</a></p>';
                                detailsHtml += '</div></div></div>';
                                
                                // Admin
                                detailsHtml += '<div class="col-md-4">';
                                detailsHtml += '<div class="card mb-3">';
                                detailsHtml += '<div class="card-header bg-info text-white">Admin</div>';
                                detailsHtml += '<div class="card-body">';
                                detailsHtml += '<p><strong>Email:</strong> ' + adminData.email + '</p>';
                                detailsHtml += '<p><strong>Mot de passe:</strong> ' + adminData.password_hint + '</p>';
                                detailsHtml += '<p><a href="' + adminData.admin_url + '/login" target="_blank" class="btn btn-sm btn-info">Se connecter</a></p>';
                                detailsHtml += '</div></div></div>';
                                
                                // User
                                detailsHtml += '<div class="col-md-4">';
                                detailsHtml += '<div class="card mb-3">';
                                detailsHtml += '<div class="card-header bg-success text-white">User</div>';
                                detailsHtml += '<div class="card-body">';
                                detailsHtml += '<p><strong>Email:</strong> ' + adminData.email + '</p>';
                                detailsHtml += '<p><strong>Mot de passe:</strong> ' + adminData.password_hint + '</p>';
                                detailsHtml += '<p><a href="' + adminData.user_url + '/login" target="_blank" class="btn btn-sm btn-success">Se connecter</a></p>';
                                detailsHtml += '</div></div></div>';
                                
                                detailsHtml += '</div>';
                                
                                // Bouton pour supprimer le dossier d'installation
                                detailsHtml += '<div class="mt-3">';
                                detailsHtml += '<button id="remove-install-dir" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo addslashes($translations["remove_install_dir"] ?? "Supprimer le dossier d\'installation"); ?></button>';
                                detailsHtml += '<p class="text-muted mt-2"><small><?php echo addslashes($translations["remove_install_dir_note"] ?? "Pour des raisons de sécurité, il est recommandé de supprimer le dossier d\'installation après avoir terminé."); ?></small></p>';
                                detailsHtml += '</div>';
                                
                                detailsHtml += '</div>';
                                
                                $('#connection-details').html(detailsHtml).removeClass('d-none');
                                
                                // Gérer le clic sur le bouton de suppression du dossier d'installation
                                $('#remove-install-dir').on('click', function() {
                                    $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> <?php echo addslashes($translations["removing"] ?? "Suppression en cours..."); ?>');
                                    
                                    $.ajax({
                                        url: 'ajax/remove_install_dir.php',
                                        type: 'POST',
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.status) {
                                                alert('<?php echo addslashes($translations["install_dir_removed"] ?? "Le dossier d\'installation a été supprimé avec succès."); ?>');
                                                window.location.href = adminData.admin_url;
                                            } else {
                                                alert('<?php echo addslashes($translations["install_dir_remove_failed"] ?? "Impossible de supprimer le dossier d\'installation. Veuillez le supprimer manuellement."); ?>');
                                                $('#remove-install-dir').prop('disabled', false).html('<i class="fas fa-trash"></i> <?php echo addslashes($translations["remove_install_dir"] ?? "Supprimer le dossier d\'installation"); ?>');
                                            }
                                        },
                                        error: function() {
                                            alert('<?php echo addslashes($translations["install_dir_remove_failed"] ?? "Impossible de supprimer le dossier d\'installation. Veuillez le supprimer manuellement."); ?>');
                                            $('#remove-install-dir').prop('disabled', false).html('<i class="fas fa-trash"></i> <?php echo addslashes($translations["remove_install_dir"] ?? "Supprimer le dossier d\'installation"); ?>');
                                        }
                                    });
                                });
                            }
                        }
                    });
                    
                    // Afficher le bouton pour aller au login
                    $('#start-installation').addClass('d-none');
                    $('#go-to-login').removeClass('d-none');
                } else {
                    // Installation échouée
                    $('#install-result').removeClass('d-none').addClass('alert-danger')
                        .html(response.message || "Erreur: L'installation a échoué. Vérifiez les logs d'installation.");
                    
                    // Journaliser l'erreur
                    addLogMessage("Erreur: L'installation a échoué.", 'error');
                    
                    // Réactiver le bouton
                    $('#start-installation').prop('disabled', false).html('<?php echo addslashes($translations["start_installation"]); ?>');
                }
            },
            error: function(xhr, status, error) {
                // Afficher un message d'erreur
                $('#install-result').removeClass('d-none').addClass('alert-danger')
                    .html("Erreur: " + error);
                
                // Journaliser l'erreur
                addLogMessage("Erreur: " + error, 'error');
                
                // Afficher les détails de l'erreur si disponibles
                if (xhr.responseText) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        var responseDetails = $('<div class="log-output mt-1 mb-2 pl-3 text-muted" style="border-left: 2px solid #dc3545; font-size: 0.85rem;"></div>');
                        responseDetails.append($('<div></div>').text('Réponse: ' + JSON.stringify(response)));
                        $('#log-content').append(responseDetails);
                    } catch (e) {
                        // Si la réponse n'est pas du JSON valide, afficher le texte brut
                        var responseDetails = $('<div class="log-output mt-1 mb-2 pl-3 text-muted" style="border-left: 2px solid #dc3545; font-size: 0.85rem;"></div>');
                        responseDetails.append($('<div></div>').text('Réponse: ' + xhr.responseText));
                        $('#log-content').append(responseDetails);
                    }
                }
                
                // Réactiver le bouton
                $('#start-installation').prop('disabled', false).html('<?php echo addslashes($translations["start_installation"]); ?>');
            }
        });
    });
});
</script>
