# AdminLicence - Système de gestion de licences

AdminLicence est un système complet de gestion de licences pour vos applications. Il permet de créer, gérer et valider des licences pour vos projets logiciels via une interface d'administration conviviale et une API sécurisée.

## Fonctionnalités

- **Gestion de projets** : Créez et gérez différents projets nécessitant des licences
- **Génération de clés de série** : Générez des clés uniques pour chaque projet
- **Validation de licences** : API sécurisée pour valider les licences dans vos applications
- **Restrictions par domaine et IP** : Limitez l'utilisation des licences à des domaines ou adresses IP spécifiques
- **Dates d'expiration** : Définissez des dates d'expiration pour vos licences
- **Gestion des emails** : Configuration personnalisée des notifications par email
- **Historique des licences** : Suivi complet des activations et modifications

## Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 5.7 ou supérieur
- Node.js et NPM (pour le développement frontend)

## Installation

1. Clonez le dépôt :
```bash
git clone [url-du-depot]
cd adminlicence
```

2. Installez les dépendances PHP :
```bash
composer install
```

3. Installez les dépendances JavaScript :
```bash
npm install
```

4. Configurez votre environnement :
```bash
cp .env.example .env
php artisan key:generate
```

5. Configurez votre base de données dans le fichier .env

6. Exécutez les migrations et les seeders :
```bash
php artisan migrate --seed
```

## Configuration

### Base de données
Modifiez le fichier `.env` pour configurer votre connexion à la base de données :
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adminlicence
DB_USERNAME=root
DB_PASSWORD=
```

### Email
Configurez vos paramètres SMTP dans l'interface d'administration ou directement dans le fichier `.env` :
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Utilisation

### Interface d'administration

1. Accédez à l'interface d'administration via `/admin/login`
2. Connectez-vous avec les identifiants par défaut :
   - Email : admin@example.com
   - Mot de passe : password

### Gestion des projets

1. Créez un nouveau projet dans l'interface d'administration
2. Configurez les paramètres de licence (durée, restrictions, etc.)
3. Générez des clés de série pour le projet

### Validation des licences

Intégrez la validation des licences dans votre application via l'API :

```php
$response = Http::post('votre-domaine/api/v1/check-serial', [
    'serial_key' => 'XXXX-XXXX-XXXX-XXXX',
    'domain' => 'example.com',
    'ip_address' => '192.168.1.1'
]);
```

### Documentation API

Une documentation complète de l'API est disponible dans l'interface d'administration via le bouton "Documentation API". Cette documentation inclut :

- Les endpoints disponibles et leurs paramètres
- Des exemples de requêtes et réponses
- Des exemples d'intégration en PHP et JavaScript
- Les bonnes pratiques pour sécuriser l'utilisation de l'API

Pour accéder à la documentation API :
1. Connectez-vous à l'interface d'administration
2. Cliquez sur le bouton "Documentation API" dans le menu latéral

## Sécurité

- Toutes les clés de série sont chiffrées en base de données
- Validation par domaine et IP pour prévenir l'utilisation non autorisée
- Système de codes dynamiques pour une sécurité renforcée
- Journalisation complète des activités de licence

## Support

Pour toute question ou assistance, veuillez :
1. Consulter la documentation complète
2. Ouvrir une issue sur le dépôt
3. Contacter le support technique

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.
# adminlicence_saas
