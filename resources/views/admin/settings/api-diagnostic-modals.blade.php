<!-- Modales pour les statistiques -->
<div class="modal fade" id="serialKeysModal" tabindex="-1" aria-labelledby="serialKeysModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="serialKeysModalLabel"><i class="fas fa-key me-2"></i>Clés de série</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Chargement des données...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="{{ route('admin.serial-keys.index') }}" class="btn btn-primary">Voir toutes les clés</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="projectsModal" tabindex="-1" aria-labelledby="projectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="projectsModalLabel"><i class="fas fa-project-diagram me-2"></i>Projets</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-success" role="status"></div>
                    <p class="mt-2">Chargement des données...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-success">Voir tous les projets</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminsModal" tabindex="-1" aria-labelledby="adminsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="adminsModalLabel"><i class="fas fa-users-cog me-2"></i>Administrateurs</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2">Chargement des données...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="activeKeysModal" tabindex="-1" aria-labelledby="activeKeysModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="activeKeysModalLabel"><i class="fas fa-check-circle me-2"></i>Clés actives</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-info" role="status"></div>
                    <p class="mt-2">Chargement des données...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="{{ route('admin.serial-keys.index') }}?status=active" class="btn btn-info">Voir toutes les clés actives</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="apiKeysModal" tabindex="-1" aria-labelledby="apiKeysModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="apiKeysModalLabel"><i class="fas fa-key me-2"></i>Clés API</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-secondary" role="status"></div>
                    <p class="mt-2">Chargement des données...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <a href="{{ route('admin.api-keys.index') }}" class="btn btn-dark">Voir toutes les clés API</a>
            </div>
        </div>
    </div>
</div>
