/**
 * Système de traduction simple pour les pages de connexion
 */
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du changement de langue directement sur la page de connexion
    const languageSelector = document.getElementById('language-selector');
    if (languageSelector) {
        languageSelector.addEventListener('change', function() {
            // Rediriger vers la page de connexion avec le paramètre de langue
            const locale = this.value;
            window.location.href = '/admin/login?locale=' + locale;
        });
    }
});
