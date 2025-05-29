<?php

namespace App\Helpers;

class AssetHelper
{
    /**
     * Génère une URL d'asset avec versioning pour une meilleure mise en cache
     *
     * @param string $path Chemin relatif de l'asset (à partir du dossier public)
     * @return string URL de l'asset avec paramètre de version
     */
    public static function versionedAsset($path)
    {
        $fullPath = public_path($path);
        
        // Vérifier si le fichier existe
        if (!file_exists($fullPath)) {
            return asset($path);
        }
        
        // Utiliser la date de modification comme version
        $timestamp = filemtime($fullPath);
        $version = '?v=' . $timestamp;
        
        return asset($path) . $version;
    }
    
    /**
     * Génère une balise script pour un fichier JavaScript avec versioning
     *
     * @param string $path Chemin relatif du fichier JS (à partir du dossier public)
     * @param array $attributes Attributs supplémentaires pour la balise script
     * @return string Balise script complète
     */
    public static function js($path, $attributes = [])
    {
        $url = self::versionedAsset($path);
        
        $attributesStr = '';
        foreach ($attributes as $key => $value) {
            $attributesStr .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        
        return '<script src="' . $url . '"' . $attributesStr . '></script>';
    }
    
    /**
     * Génère une balise link pour un fichier CSS avec versioning
     *
     * @param string $path Chemin relatif du fichier CSS (à partir du dossier public)
     * @param array $attributes Attributs supplémentaires pour la balise link
     * @return string Balise link complète
     */
    public static function css($path, $attributes = [])
    {
        $url = self::versionedAsset($path);
        
        $attributesStr = '';
        foreach ($attributes as $key => $value) {
            $attributesStr .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        
        return '<link href="' . $url . '" rel="stylesheet"' . $attributesStr . '>';
    }
    
    /**
     * Génère une URL d'image avec versioning
     *
     * @param string $path Chemin relatif de l'image (à partir du dossier public)
     * @return string URL de l'image avec paramètre de version
     */
    public static function image($path)
    {
        return self::versionedAsset($path);
    }
}
