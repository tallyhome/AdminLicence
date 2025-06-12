<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Système Unifié - Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système unifié mono-compte/SaaS
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Mode par défaut
    |--------------------------------------------------------------------------
    |
    | Le mode par défaut du système si aucune licence n'est détectée
    | Valeurs possibles: 'single', 'saas'
    |
    */
    'default_mode' => env('UNIFIED_SYSTEM_DEFAULT_MODE', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Configuration du cache pour le système unifié
    |
    */
    'cache' => [
        'enabled' => env('UNIFIED_SYSTEM_CACHE_ENABLED', true),
        'ttl' => env('UNIFIED_SYSTEM_CACHE_TTL', 3600), // 1 heure
        'key_prefix' => 'unified_system:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonctionnalités
    |--------------------------------------------------------------------------
    |
    | Configuration des fonctionnalités disponibles par mode
    |
    */
    'features' => [
        'single' => [
            'project_management' => true,
            'api_access' => true,
            'email_templates' => true,
            'basic_support' => true,
            'multi_tenant' => false,
            'advanced_analytics' => false,
            'priority_support' => false,
            'white_label' => false,
            'custom_domains' => false,
            'billing_management' => false,
        ],
        'saas' => [
            'project_management' => true,
            'api_access' => true,
            'email_templates' => true,
            'basic_support' => true,
            'multi_tenant' => true,
            'advanced_analytics' => true,
            'priority_support' => true,
            'white_label' => true,
            'custom_domains' => true,
            'billing_management' => true,
            'tenant_management' => true,
            'subscription_management' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites par défaut
    |--------------------------------------------------------------------------
    |
    | Limites par défaut pour chaque mode
    | null = illimité
    |
    */
    'limits' => [
        'single' => [
            'projects' => 10,
            'tenants' => 1,
            'clients_per_tenant' => 1,
            'api_calls_per_month' => 10000,
            'email_templates' => 5,
            'storage_gb' => 1,
            'admin_users' => 3,
        ],
        'saas' => [
            'projects' => null, // Illimité
            'tenants' => 100,
            'clients_per_tenant' => 1000,
            'api_calls_per_month' => 1000000,
            'email_templates' => null, // Illimité
            'storage_gb' => 100,
            'admin_users' => null, // Illimité
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes restreintes
    |--------------------------------------------------------------------------
    |
    | Routes qui ne sont accessibles que dans certains modes
    |
    */
    'restricted_routes' => [
        'saas_only' => [
            'admin.tenants.*',
            'admin.subscriptions.*',
            'admin.billing.*',
            'admin.plans.*',
        ],
        'single_only' => [
            // Routes spécifiques au mode mono-compte
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Configuration du middleware de mode de licence
    |
    */
    'middleware' => [
        'enabled' => env('UNIFIED_SYSTEM_MIDDLEWARE_ENABLED', true),
        'redirect_unauthorized' => env('UNIFIED_SYSTEM_REDIRECT_UNAUTHORIZED', true),
        'unauthorized_route' => 'admin.dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Simulation (pour les tests)
    |--------------------------------------------------------------------------
    |
    | Permet de simuler différents modes en environnement local
    |
    */
    'simulation' => [
        'enabled' => env('UNIFIED_SYSTEM_SIMULATION_ENABLED', env('APP_ENV') === 'local'),
        'session_key' => 'simulated_licence_mode',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configuration des notifications liées aux limites et changements de mode
    |
    */
    'notifications' => [
        'limit_warnings' => [
            'enabled' => true,
            'threshold_percentage' => 80, // Avertir à 80% de la limite
        ],
        'mode_changes' => [
            'enabled' => true,
            'notify_admins' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Intégrations
    |--------------------------------------------------------------------------
    |
    | Configuration des intégrations avec d'autres services
    |
    */
    'integrations' => [
        'analytics' => [
            'enabled' => env('UNIFIED_SYSTEM_ANALYTICS_ENABLED', false),
            'provider' => env('UNIFIED_SYSTEM_ANALYTICS_PROVIDER', 'google'),
        ],
        'billing' => [
            'enabled' => env('UNIFIED_SYSTEM_BILLING_ENABLED', false),
            'provider' => env('UNIFIED_SYSTEM_BILLING_PROVIDER', 'stripe'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Labels et traductions
    |--------------------------------------------------------------------------
    |
    | Labels utilisés dans l'interface utilisateur
    |
    */
    'labels' => [
        'single' => [
            'fr' => 'Mode Mono-compte',
            'en' => 'Single Account Mode',
        ],
        'saas' => [
            'fr' => 'Mode SaaS Multi-tenants',
            'en' => 'SaaS Multi-tenant Mode',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité
    |--------------------------------------------------------------------------
    |
    | Paramètres de sécurité pour le système unifié
    |
    */
    'security' => [
        'require_licence_validation' => env('UNIFIED_SYSTEM_REQUIRE_LICENCE', true),
        'allow_mode_switching' => env('UNIFIED_SYSTEM_ALLOW_MODE_SWITCHING', false),
        'log_mode_changes' => env('UNIFIED_SYSTEM_LOG_MODE_CHANGES', true),
    ],
];