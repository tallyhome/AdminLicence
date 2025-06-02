/**
 * Script ultra-simplifié pour la gestion des messages flash
 */

// Fonction pour fermer un message flash
function closeFlashMessage(id) {
    console.log('Fermeture du message:', id);
    const element = document.getElementById(id);
    if (element) {
        element.style.opacity = '0';
        setTimeout(() => element.style.display = 'none', 500);
    }
}

// Rendre la fonction disponible globalement
window.closeFlashMessage = closeFlashMessage;

// Fonction pour initialiser la fermeture automatique
function setupAutoClose() {
    console.log('Configuration de la fermeture automatique');
    const messages = document.querySelectorAll('.flash-message, [role="alert"]');
    console.log('Messages trouvés:', messages.length);
    
    if (messages.length > 0) {
        setTimeout(() => {
            messages.forEach(msg => {
                if (msg.id) {
                    console.log('Fermeture automatique de:', msg.id);
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.style.display = 'none', 500);
                }
            });
        }, 5000);
    }
}

// Exécuter dès que possible
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupAutoClose);
} else {
    setupAutoClose();
}
