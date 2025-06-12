<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mode de facturation
    |--------------------------------------------------------------------------
    |
    | Détermine le mode de fonctionnement de l'application :
    | - 'saas' : Mode SaaS avec abonnements multiples
    | - 'license' : Mode licence traditionnelle
    |
    */
    'mode' => env('BILLING_MODE', 'license'),

    /*
    |--------------------------------------------------------------------------
    | Configuration générale
    |--------------------------------------------------------------------------
    */
    'enabled' => env('BILLING_ENABLED', false),
    'currency' => env('BILLING_CURRENCY', 'EUR'),
    'tax_rate' => env('BILLING_TAX_RATE', 20.0),
    'invoice_prefix' => env('BILLING_INVOICE_PREFIX', 'INV-'),

    /*
    |--------------------------------------------------------------------------
    | Périodes d'essai et de grâce
    |--------------------------------------------------------------------------
    */
    'trial_days_default' => env('BILLING_TRIAL_DAYS', 14),
    'grace_period_days' => env('BILLING_GRACE_PERIOD_DAYS', 7),
    'auto_suspend_after_days' => env('BILLING_AUTO_SUSPEND_DAYS', 3),
    'auto_delete_after_days' => env('BILLING_AUTO_DELETE_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Configuration Stripe
    |--------------------------------------------------------------------------
    */
    'stripe' => [
        'enabled' => env('STRIPE_ENABLED', false),
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'webhook_tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration PayPal
    |--------------------------------------------------------------------------
    */
    'paypal' => [
        'enabled' => env('PAYPAL_ENABLED', false),
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox ou live
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        'webhook_secret' => env('PAYPAL_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Plans de facturation
    |--------------------------------------------------------------------------
    |
    | Configuration des plans disponibles. Ces données peuvent être
    | surchargées par la base de données.
    |
    */
    'plans' => [
        'basic' => [
            'name' => 'Plan Basic',
            'description' => 'Plan de base pour les petites entreprises',
            'price_monthly' => 29.99,
            'price_yearly' => 299.99,
            'currency' => 'EUR',
            'features' => [
                'Jusqu\'à 5 projets',
                '100 clés de licence',
                '10 clés API',
                'Support email',
                'Tableau de bord basique'
            ],
            'limits' => [
                'projects' => 5,
                'license_keys' => 100,
                'api_keys' => 10,
                'users' => 3,
                'storage_gb' => 1
            ],
            'stripe_price_id_monthly' => env('STRIPE_PRICE_BASIC_MONTHLY'),
            'stripe_price_id_yearly' => env('STRIPE_PRICE_BASIC_YEARLY'),
            'paypal_plan_id_monthly' => env('PAYPAL_PLAN_BASIC_MONTHLY'),
            'paypal_plan_id_yearly' => env('PAYPAL_PLAN_BASIC_YEARLY'),
            'trial_days' => 14,
            'popular' => false,
            'active' => true
        ],
        'premium' => [
            'name' => 'Plan Premium',
            'description' => 'Plan avancé pour les entreprises en croissance',
            'price_monthly' => 79.99,
            'price_yearly' => 799.99,
            'currency' => 'EUR',
            'features' => [
                'Jusqu\'à 25 projets',
                '1000 clés de licence',
                '50 clés API',
                'Support prioritaire',
                'Tableau de bord avancé',
                'Rapports détaillés',
                'Intégrations webhook'
            ],
            'limits' => [
                'projects' => 25,
                'license_keys' => 1000,
                'api_keys' => 50,
                'users' => 10,
                'storage_gb' => 10
            ],
            'stripe_price_id_monthly' => env('STRIPE_PRICE_PREMIUM_MONTHLY'),
            'stripe_price_id_yearly' => env('STRIPE_PRICE_PREMIUM_YEARLY'),
            'paypal_plan_id_monthly' => env('PAYPAL_PLAN_PREMIUM_MONTHLY'),
            'paypal_plan_id_yearly' => env('PAYPAL_PLAN_PREMIUM_YEARLY'),
            'trial_days' => 14,
            'popular' => true,
            'active' => true
        ],
        'enterprise' => [
            'name' => 'Plan Enterprise',
            'description' => 'Plan complet pour les grandes entreprises',
            'price_monthly' => 199.99,
            'price_yearly' => 1999.99,
            'currency' => 'EUR',
            'features' => [
                'Projets illimités',
                'Clés de licence illimitées',
                'Clés API illimitées',
                'Support dédié 24/7',
                'Tableau de bord personnalisé',
                'Rapports avancés',
                'Intégrations complètes',
                'SLA garanti',
                'Formation incluse'
            ],
            'limits' => [
                'projects' => -1, // -1 = illimité
                'license_keys' => -1,
                'api_keys' => -1,
                'users' => -1,
                'storage_gb' => -1
            ],
            'stripe_price_id_monthly' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY'),
            'stripe_price_id_yearly' => env('STRIPE_PRICE_ENTERPRISE_YEARLY'),
            'paypal_plan_id_monthly' => env('PAYPAL_PLAN_ENTERPRISE_MONTHLY'),
            'paypal_plan_id_yearly' => env('PAYPAL_PLAN_ENTERPRISE_YEARLY'),
            'trial_days' => 30,
            'popular' => false,
            'active' => true
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs de redirection
    |--------------------------------------------------------------------------
    */
    'urls' => [
        'success' => env('BILLING_SUCCESS_URL', '/billing/success'),
        'cancel' => env('BILLING_CANCEL_URL', '/billing/cancel'),
        'webhook_stripe' => env('BILLING_WEBHOOK_STRIPE_URL', '/webhooks/stripe'),
        'webhook_paypal' => env('BILLING_WEBHOOK_PAYPAL_URL', '/webhooks/paypal'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'payment_failed' => [
            'enabled' => env('BILLING_NOTIFY_PAYMENT_FAILED', true),
            'retry_attempts' => 3,
            'retry_delay_hours' => 24,
        ],
        'subscription_ending' => [
            'enabled' => env('BILLING_NOTIFY_SUBSCRIPTION_ENDING', true),
            'days_before' => [30, 7, 1],
        ],
        'trial_ending' => [
            'enabled' => env('BILLING_NOTIFY_TRIAL_ENDING', true),
            'days_before' => [7, 3, 1],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Limites par défaut
    |--------------------------------------------------------------------------
    */
    'default_limits' => [
        'trial' => [
            'projects' => 2,
            'license_keys' => 10,
            'api_keys' => 2,
            'users' => 1,
            'storage_gb' => 0.5
        ],
        'free' => [
            'projects' => 1,
            'license_keys' => 5,
            'api_keys' => 1,
            'users' => 1,
            'storage_gb' => 0.1
        ]
    ],
];