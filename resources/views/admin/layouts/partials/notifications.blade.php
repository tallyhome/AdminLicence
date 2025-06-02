<div class="dropdown">
    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="font-size: 0.5rem;">
            <span class="unread-count">0</span>
            <span class="visually-hidden">notifications non lues</span>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" id="notification-list" style="width: 400px; min-width: 400px;">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <h6 class="m-0">Notifications</h6>
            <button class="btn btn-sm btn-link text-decoration-none mark-all-read">Tout marquer comme lu</button>
        </div>
        <div class="notifications-container" style="max-height: 500px; overflow-y: auto;">
            <div class="text-center py-3 loading-notifications">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mb-0 mt-2">Chargement des notifications...</p>
            </div>
            <div class="notifications-list" style="display: none;"></div>
            <div class="no-notifications text-center py-3" style="display: none;">
                <i class="fas fa-check-circle text-success mb-2" style="font-size: 1.5rem;"></i>
                <p class="mb-0">Aucune notification</p>
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-center" href="{{ route('admin.notifications.index') }}">Voir toutes les notifications</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le dropdown des notifications
        const notificationsDropdown = document.getElementById('notificationsDropdown');
        if (notificationsDropdown) {
            // Utiliser l'API Bootstrap pour initialiser le dropdown
            const dropdownInstance = new bootstrap.Dropdown(notificationsDropdown);
            
            // Ajouter un gestionnaire d'événements pour le clic
            notificationsDropdown.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Charger les notifications à chaque ouverture du dropdown
                loadNotifications();
            });
        }
        
        // Fonction pour charger les notifications
        function loadNotifications() {
            const container = document.querySelector('.notifications-container');
            const loadingEl = container.querySelector('.loading-notifications');
            const listEl = container.querySelector('.notifications-list');
            const noNotificationsEl = container.querySelector('.no-notifications');
            
            // Afficher le chargement
            loadingEl.style.display = 'block';
            listEl.style.display = 'none';
            noNotificationsEl.style.display = 'none';
            
            // Simuler le chargement (à remplacer par un appel AJAX réel)
            setTimeout(function() {
                // Cacher le chargement
                loadingEl.style.display = 'none';
                
                // Mettre à jour le compteur de notifications non lues
                document.querySelector('.unread-count').textContent = '0';
                
                // Afficher le message "aucune notification"
                noNotificationsEl.style.display = 'block';
            }, 1000);
        }
        
        // Gestionnaire pour "Tout marquer comme lu"
        const markAllReadBtn = document.querySelector('.mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Simuler le marquage comme lu (à remplacer par un appel AJAX réel)
                document.querySelector('.unread-count').textContent = '0';
                alert('Toutes les notifications ont été marquées comme lues');
            });
        }
    });
</script>

<!-- Conteneur pour les toasts de notification -->
<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

<!-- Script pour initialiser les variables nécessaires aux notifications -->
@php use Illuminate\Support\Facades\Auth; @endphp
<script>
    // ID de l'utilisateur connecté pour les notifications privées
    window.userId = {{ Auth::guard('admin')->id() ?? 'null' }};
    
    // Compteur initial de notifications non lues
    let unreadNotificationsCount = 0;
</script>