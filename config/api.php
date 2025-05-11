<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permissions API
    |--------------------------------------------------------------------------
    |
    | Liste des permissions disponibles pour les clés API.
    |
    */

    'permissions' => [
        'licences:read' => 'permissions.license_read',
        'licences:write' => 'permissions.license_write',
        'licences:delete' => 'permissions.license_delete',
        'projects:read' => 'permissions.project_read',
        'projects:write' => 'permissions.project_write',
        'projects:delete' => 'permissions.project_delete',
        'users:read' => 'permissions.user_read',
        'users:write' => 'permissions.user_write',
        'users:delete' => 'permissions.user_delete',
        'statistics:read' => 'permissions.stats_read',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration du rate limiting pour l'API.
    |
    */

    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Expiration
    |--------------------------------------------------------------------------
    |
    | Configuration de l'expiration des tokens API.
    |
    */

    'token_expiration' => [
        'enabled' => true,
        'default_expiration' => 30, // jours
    ],
]; 