# Configuration du Système de Facturation

## Installation des Dépendances

Pour activer complètement le système de facturation, vous devez installer les dépendances suivantes :

### 1. Stripe PHP SDK
```bash
composer require stripe/stripe-php:^10.0
```

### 2. PayPal SDK (optionnel)
```bash
composer require srmklive/paypal:^3.0
```

### 3. Pusher pour les WebSockets (optionnel)
```bash
composer require pusher/pusher-php-server:^7.2
```

## Configuration

### Variables d'environnement
Ajoutez ces variables à votre fichier `.env` :

```env
# Stripe Configuration
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# PayPal Configuration
PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=your_sandbox_client_id
PAYPAL_SANDBOX_CLIENT_SECRET=your_sandbox_client_secret
PAYPAL_LIVE_CLIENT_ID=your_live_client_id
PAYPAL_LIVE_CLIENT_SECRET=your_live_client_secret

# Pusher Configuration (pour les notifications en temps réel)
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Configuration des services
Mettez à jour `config/services.php` :

```php
'stripe' => [
    'model' => App\Models\User::class,
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],
],

'paypal' => [
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET'),
    ],
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
        'client_secret' => env('PAYPAL_LIVE_CLIENT_SECRET'),
    ],
],
```

## Migrations

Exécutez les migrations pour créer les tables de facturation :

```bash
php artisan migrate
```

## Seeders

Exécutez les seeders pour créer les plans de facturation par défaut :

```bash
php artisan db:seed --class=BillingPlansSeeder
```

## Commandes Artisan

### Traitement des abonnements expirés
```bash
php artisan billing:process-expired
```

### Synchronisation avec les fournisseurs
```bash
php artisan billing:sync-subscriptions
```

### Notifications de facturation
```bash
php artisan queue:work
```

## Tests

Exécutez les tests de facturation :

```bash
php artisan test --filter=Billing
```

## Notes importantes

1. **Mode SaaS requis** : Le système de facturation ne fonctionne qu'en mode SaaS
2. **Webhooks** : Configurez les webhooks Stripe et PayPal pour pointer vers votre application
3. **Queue** : Assurez-vous que le système de queue est configuré pour les notifications
4. **SSL** : Utilisez HTTPS en production pour les paiements

## Dépannage

Si vous rencontrez l'erreur `Target class [check.licence.mode] does not exist` :

1. Videz le cache :
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

2. Vérifiez que le service provider est enregistré dans `config/app.php`

3. Régénérez l'autoloader :
```bash
composer dump-autoload
```