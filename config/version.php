<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Version de l'application
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient les informations de version de l'application.
    | Ces informations sont utilisées pour afficher la version actuelle
    | dans l'interface d'administration.
    |
    */

    'major' => 3,      // Changements majeurs/incompatibles
    'minor' => 3,      // Nouvelles fonctionnalités compatibles
    'patch' => 1,      // Corrections de bugs compatibles
    'release' => null, // Suffixe de version (alpha, beta, rc, etc.),
     
    // Date de la dernière mise à jour
    'last_update' => '10/04/2025',
    
    // Fonction pour obtenir la version complète formatée
    'full' => function () {
        $version = config('version.major') . '.' . config('version.minor') . '.' . config('version.patch');
        if (config('version.release')) {
            $version .= '-' . config('version.release');
        }
        return $version;
    },
];