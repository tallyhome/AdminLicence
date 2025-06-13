# Guide d'installation optimisé pour AdminLicence

Ce dossier contient le système d'installation pour AdminLicence. Le système a été simplifié et optimisé pour être plus facile à utiliser et à maintenir.

## Structure des fichiers

```
/install/
├── index.php                 # Script d'installation principal
├── install_log.txt           # Fichier de log pour le débogage
├── config.php                # Configuration de base
├── README.md                 # Ce guide d'utilisation
├── functions/                # Dossier contenant les fonctions modulaires
│   ├── core.php              # Fonctions essentielles
│   ├── database.php          # Fonctions liées à la base de données
│   ├── installation.php      # Fonctions d'installation
│   ├── language.php          # Fonctions de gestion des langues
│   └── ui.php                # Fonctions d'interface utilisateur
└── languages/                # Dossier contenant les fichiers de langue
    ├── fr.php                # Traductions françaises
    ├── en.php                # Traductions anglaises
    └── ...                   # Autres langues
```

## Comment utiliser le système d'installation

Pour utiliser le système d'installation, accédez simplement à :

```
http://votre-domaine/install/index.php?step=1
```

Le système d'installation vous guidera à travers les étapes suivantes :
1. Vérification de la licence
2. Configuration de la base de données
3. Configuration du compte administrateur
4. Installation finale

## Avantages du système

- **Modularité** : Le code est organisé en modules fonctionnels, ce qui facilite la maintenance et les mises à jour.
- **Performance** : Le code a été optimisé pour être plus rapide et consommer moins de ressources.
- **Multilingue** : Le système de gestion des langues a été amélioré pour être plus flexible et plus facile à étendre.
- **Robustesse** : Le système gère mieux les erreurs et les cas particuliers, comme les migrations échouées.
- **Débogage** : Un système de journalisation amélioré permet de suivre et de résoudre les problèmes plus facilement.

## Ajouter une nouvelle langue

Pour ajouter une nouvelle langue au système d'installation, suivez ces étapes :

1. Créez un nouveau fichier dans le dossier `languages/` avec le code de la langue comme nom de fichier (par exemple, `es.php` pour l'espagnol).
2. Copiez le contenu d'un fichier de langue existant (comme `fr.php`) et traduisez les valeurs.
3. Ajoutez le code de la langue et son nom dans la constante `AVAILABLE_LANGUAGES` dans le fichier `config.php`.

Exemple pour ajouter l'italien :

```php
// Dans config.php
define('AVAILABLE_LANGUAGES', [
    'fr' => 'Français',
    'en' => 'English',
    'es' => 'Español',
    'it' => 'Italiano', // Nouvelle langue
    // ...
]);
```

## Installation propre

Le système d'installation a été simplifié. Il n'y a plus qu'un seul point d'entrée (`index.php`) qui gère automatiquement toutes les étapes d'installation.

## Dépannage

Si vous rencontrez des problèmes lors de l'installation, consultez le fichier `install_log.txt` qui contient des informations détaillées sur les erreurs rencontrées.

Pour forcer une réinstallation même si le système considère qu'AdminLicence est déjà installé, ajoutez le paramètre `force=1` à l'URL :

```
http://votre-domaine/install/index.php?step=1&force=1
```

## Support

Pour toute question ou problème concernant l'installation, veuillez contacter le support technique.
