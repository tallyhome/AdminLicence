/**
 * Script pour gérer le thème sombre
 */
document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour appliquer le mode sombre
    function applyDarkMode() {
        document.body.classList.add('dark-mode');
        
        // Appliquer des styles spécifiques au pied de page
        const footer = document.querySelector('footer');
        if (footer) {
            footer.style.backgroundColor = '#1e1e1e';
            footer.style.color = '#e0e0e0';
            footer.style.borderColor = '#333';
            
            // Appliquer des styles aux liens dans le pied de page
            const footerLinks = footer.querySelectorAll('a');
            footerLinks.forEach(link => {
                link.style.color = '#4e73df';
                link.addEventListener('mouseover', function() {
                    this.style.color = '#6c8ae4';
                });
                link.addEventListener('mouseout', function() {
                    this.style.color = '#4e73df';
                });
            });
        }
        
        // Appliquer des styles spécifiques à l'en-tête
        const nav = document.querySelector('nav');
        if (nav) {
            // Changer la couleur du texte des éléments de langue et du nom d'utilisateur
            const grayTextElements = nav.querySelectorAll('.text-gray-700, .text-gray-500, .text-gray-600, .text-gray-800, .text-gray-900');
            grayTextElements.forEach(element => {
                element.style.color = '#e0e0e0';
            });
            
            // Changer la couleur des icônes SVG
            const svgElements = nav.querySelectorAll('svg');
            svgElements.forEach(svg => {
                svg.style.color = '#e0e0e0';
            });
            
            // Changer le fond des menus déroulants
            const dropdownMenus = nav.querySelectorAll('.bg-white');
            dropdownMenus.forEach(menu => {
                menu.style.backgroundColor = '#1e1e1e';
                menu.style.borderColor = '#333';
                
                // Changer la couleur du texte dans les menus déroulants
                const menuItems = menu.querySelectorAll('button, a');
                menuItems.forEach(item => {
                    item.style.color = '#e0e0e0';
                    item.addEventListener('mouseover', function() {
                        this.style.backgroundColor = '#333';
                    });
                    item.addEventListener('mouseout', function() {
                        this.style.backgroundColor = '#1e1e1e';
                    });
                });
            });
        }
    }
    
    // Fonction pour supprimer le mode sombre
    function removeDarkMode() {
        document.body.classList.remove('dark-mode');
        
        // Réinitialiser les styles du pied de page
        const footer = document.querySelector('footer');
        if (footer) {
            footer.style.backgroundColor = '';
            footer.style.color = '';
            footer.style.borderColor = '';
            
            // Réinitialiser les styles des liens dans le pied de page
            const footerLinks = footer.querySelectorAll('a');
            footerLinks.forEach(link => {
                link.style.color = '';
                link.removeEventListener('mouseover', function() {});
                link.removeEventListener('mouseout', function() {});
            });
        }
        
        // Réinitialiser les styles de l'en-tête
        const nav = document.querySelector('nav');
        if (nav) {
            // Réinitialiser la couleur du texte des éléments de langue et du nom d'utilisateur
            const grayTextElements = nav.querySelectorAll('.text-gray-700, .text-gray-500, .text-gray-600, .text-gray-800, .text-gray-900');
            grayTextElements.forEach(element => {
                element.style.color = '';
            });
            
            // Réinitialiser la couleur des icônes SVG
            const svgElements = nav.querySelectorAll('svg');
            svgElements.forEach(svg => {
                svg.style.color = '';
            });
            
            // Réinitialiser le fond des menus déroulants
            const dropdownMenus = nav.querySelectorAll('.bg-white');
            dropdownMenus.forEach(menu => {
                menu.style.backgroundColor = '';
                menu.style.borderColor = '';
                
                // Réinitialiser la couleur du texte dans les menus déroulants
                const menuItems = menu.querySelectorAll('button, a');
                menuItems.forEach(item => {
                    item.style.color = '';
                    item.removeEventListener('mouseover', function() {});
                    item.removeEventListener('mouseout', function() {});
                });
            });
        }
    }
    
    // Vérifier si le thème sombre est activé dans la session
    const darkModeEnabled = document.body.classList.contains('dark-mode');
    
    // Appliquer le thème sombre si activé
    if (darkModeEnabled) {
        applyDarkMode();
    }
    
    // Écouter les changements de thème depuis la page des paramètres
    const darkModeToggle = document.getElementById('dark_mode');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                applyDarkMode();
            } else {
                removeDarkMode();
            }
        });
    }
});