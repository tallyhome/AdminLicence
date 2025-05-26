// Fichier de gestion des traductions côté client

// Objet global pour stocker les traductions
window.translations = {};

// Fonction pour charger les traductions depuis le serveur
async function loadTranslations(locale) {
    try {
        // Récupérer les traductions de la langue actuelle
        const response = await fetch(`/api/translations?locale=${locale}`);
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
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
