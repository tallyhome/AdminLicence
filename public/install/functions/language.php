<?php
/**
 * Fonctions de gestion des langues pour l'installateur
 */

/**
 * Initialise la langue en fonction des préférences de l'utilisateur
 * 
 * @return string Code de la langue sélectionnée
 */
function initLanguage() {
    // Vérifier si une langue est déjà définie en session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Si la langue est spécifiée dans l'URL, l'utiliser
    if (isset($_GET['language']) && array_key_exists($_GET['language'], AVAILABLE_LANGUAGES)) {
        $_SESSION['installer_language'] = $_GET['language'];
        return $_SESSION['installer_language'];
    }
    
    // Si la langue est spécifiée dans le formulaire, l'utiliser
    if (isset($_POST['language']) && array_key_exists($_POST['language'], AVAILABLE_LANGUAGES)) {
        $_SESSION['installer_language'] = $_POST['language'];
        return $_SESSION['installer_language'];
    }
    
    // Si la langue est déjà définie en session, l'utiliser
    if (isset($_SESSION['installer_language']) && array_key_exists($_SESSION['installer_language'], AVAILABLE_LANGUAGES)) {
        return $_SESSION['installer_language'];
    }
    
    // Sinon, détecter la langue du navigateur
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'fr', 0, 2);
    $_SESSION['installer_language'] = array_key_exists($browserLang, AVAILABLE_LANGUAGES) ? $browserLang : DEFAULT_LANGUAGE;
    
    return $_SESSION['installer_language'];
}

/**
 * Charge les traductions pour une langue donnée
 * 
 * @param string $lang Code de la langue
 * @return array Tableau des traductions
 */
function loadTranslations($lang) {
    $langFile = INSTALL_PATH . '/languages/' . $lang . '.php';
    
    if (file_exists($langFile)) {
        return include $langFile;
    }
    
    // Fallback sur la langue par défaut
    $defaultLangFile = INSTALL_PATH . '/languages/' . DEFAULT_LANGUAGE . '.php';
    if (file_exists($defaultLangFile)) {
        return include $defaultLangFile;
    }
    
    // Si aucun fichier de langue n'est trouvé, retourner un tableau vide
    return [];
}

/**
 * Fonction de traduction
 * 
 * @param string $key Clé de traduction
 * @param array $replacements Remplacements à effectuer
 * @return string Texte traduit
 */
function t($key, $replacements = []) {
    static $translations = null;
    static $currentLang = null;
    
    // Si la langue a changé ou si les traductions n'ont pas encore été chargées
    if ($translations === null || $currentLang !== getCurrentLanguage()) {
        $currentLang = getCurrentLanguage();
        $translations = loadTranslations($currentLang);
    }
    
    $text = $translations[$key] ?? $key;
    
    // Appliquer les remplacements
    if (!empty($replacements)) {
        foreach ($replacements as $placeholder => $value) {
            $text = str_replace(':' . $placeholder, $value, $text);
        }
    }
    
    return $text;
}

/**
 * Obtenir la langue actuelle
 * 
 * @return string Code de la langue actuelle
 */
function getCurrentLanguage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['installer_language']) || !array_key_exists($_SESSION['installer_language'], AVAILABLE_LANGUAGES)) {
        return initLanguage();
    }
    
    return $_SESSION['installer_language'];
}

/**
 * Obtenir les langues disponibles avec leurs noms
 * 
 * @return array Tableau des langues disponibles
 */
function getAvailableLanguages() {
    return AVAILABLE_LANGUAGES;
}

/**
 * Générer les liens de changement de langue
 * 
 * @return string HTML des liens de changement de langue
 */
function getLanguageLinks() {
    $currentLang = getCurrentLanguage();
    $links = [];
    
    // Conserver les paramètres actuels de l'URL
    $params = $_GET;
    unset($params['language']); // Supprimer le paramètre language s'il existe
    $queryString = !empty($params) ? '&' . http_build_query($params) : '';
    
    foreach (AVAILABLE_LANGUAGES as $code => $name) {
        $active = $code === $currentLang ? ' class="active"' : '';
        $links[] = sprintf('<a href="?language=%s%s"%s>%s</a>', $code, $queryString, $active, $name);
    }
    
    return implode(' ', $links);
}
