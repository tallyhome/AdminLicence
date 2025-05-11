<!-- Composant de notifications en temps réel -->
<div id="notification-container" class="dropdown">
    <button class="btn btn-link nav-link dropdown-toggle" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span id="notification-counter" class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle <?php echo e($unreadNotificationsCount ?? 0 > 0 ? '' : 'hidden'); ?>">0</span>
    </button>
    <ul id="notification-list" class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 400px; max-height: 600px; overflow-y: auto;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            <button id="mark-all-as-read" class="btn btn-sm btn-link text-decoration-none">Tout marquer comme lu</button>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li class="py-2 px-4 text-center text-muted">Aucune notification</li>
    </ul>
</div>

<!-- Conteneur pour les toasts de notification -->
<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

<!-- Script pour initialiser les variables nécessaires aux notifications -->
<?php use Illuminate\Support\Facades\Auth; ?>
<script>
    // ID de l'utilisateur connecté pour les notifications privées
    window.userId = <?php echo e(Auth::guard('admin')->id() ?? 'null'); ?>;
    
    // Compteur initial de notifications non lues
    let unreadNotificationsCount = 0;
</script><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/layouts/partials/notifications.blade.php ENDPATH**/ ?>