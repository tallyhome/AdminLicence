// Fichier de gestion des traductions côté client

// Objet global pour stocker les traductions
window.translations = {};

// Fonction pour charger les traductions depuis le serveur
async function loadTranslations(locale) {
    try {
        // Vérifier d'abord si nous avons des traductions statiques disponibles
        if (window.staticTranslations && window.staticTranslations[locale]) {
            console.log(`Utilisation des traductions statiques pour: ${locale}`);
            window.translations = window.staticTranslations[locale];
            translatePage();
            return;
        }
        
        // Si pas de traductions statiques, essayer les routes dynamiques
        const routes = [
            `/web-translations?locale=${locale}`,     // Route web standard (utilise TranslationController) - FONCTIONNE
            `/api/translations.php?locale=${locale}`, // Route API directe PHP - FONCTIONNE
            `/test-direct-translations.php?locale=${locale}`, // Route de test directe - FONCTIONNE
            `/direct-translations?locale=${locale}`,  // Route alternative (utilise TranslationController)
            `/translations.php`,                      // Point d'entrée direct PHP (fallback)
        ];
        
        let response = null;
        let success = false;
        
        // Essayer chaque route jusqu'à ce qu'une fonctionne
        for (const route of routes) {
            try {
                console.log(`Tentative de chargement des traductions depuis: ${route}`);
                response = await fetch(route);
                
                if (response.ok) {
                    console.log(`Succès avec la route: ${route}`);
                    success = true;
                    break;
                } else {
                    console.log(`Échec avec la route: ${route} (${response.status})`);
                }
            } catch (routeError) {
                console.log(`Erreur avec la route: ${route}`, routeError);
            }
        }
        
        if (!success) {
            console.error(`Toutes les routes de traduction ont échoué, utilisation des traductions par défaut`);
            // Utiliser des traductions par défaut minimales en cas d'échec total
            window.translations = {
                common: {
                    add: "Add",
                    dashboard: "Dashboard",
                    save: "Save",
                    cancel: "Cancel",
                    delete: "Delete",
                    edit: "Edit"
                }
            };
            translatePage();
            return;
        }
        
        const data = await response.json();
        
        // Stocker les traductions dans l'objet global
        window.translations = data;
        
        console.log(`Traductions chargées pour la langue: ${locale}`);
        
        // Mettre à jour les éléments traduits dans la page
        translatePage();
    } catch (error) {
        console.error('Erreur lors du chargement des traductions:', error);
    }
}

// Fonction pour traduire un texte
window.__ = function(key, replacements = {}) {
    // Diviser la clé par les points pour accéder aux objets imbriqués
    const keys = key.split('.');
    let translation = window.translations;
    
    // Parcourir les clés pour accéder à la traduction
    for (const k of keys) {
        if (!translation || !translation[k]) {
            // Si la traduction n'existe pas, retourner la clé
            return key;
        }
        translation = translation[k];
    }
    
    // Si la traduction est un objet, retourner la clé
    if (typeof translation === 'object') {
        return key;
    }
    
    // Remplacer les variables dans la traduction
    let result = translation;
    for (const [placeholder, value] of Object.entries(replacements)) {
        result = result.replace(`:${placeholder}`, value);
    }
    
    return result;
};

// Fonction pour traduire tous les éléments de la page avec l'attribut data-i18n
function translatePage() {
    document.querySelectorAll('[data-i18n]').forEach(element => {
        const key = element.getAttribute('data-i18n');
        element.textContent = window.__(key);
    });
}

// Charger les traductions au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    // Récupérer la langue depuis l'attribut lang de la balise html
    const locale = document.documentElement.lang || 'fr';
    loadTranslations(locale);
    
    // Ajouter un gestionnaire d'événements pour le changement de langue
    document.querySelectorAll('.language-selector select').forEach(select => {
        select.addEventListener('change', function() {
            const newLocale = this.value;
            // Charger les traductions pour la nouvelle langue
            loadTranslations(newLocale);
            // Rediriger vers la route de changement de langue
            window.location.href = `/set-locale?locale=${newLocale}`;
        });
    });
});

export { loadTranslations, translatePage };
