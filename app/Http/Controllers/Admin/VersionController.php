<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    /**
     * Afficher les informations de version de l'application.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $version = [
            'major' => 3,
            'minor' => 3,
            'patch' => 1,
            'release' => null,
            'full' => config('version.full')(),
            'last_update' => '10/04/2025',
        ];
        
        // Historique des versions
        $history = [
            [
                'version' => '3.3.1',
                'date' => '10/04/2025',
                'description' => 'Corrections de bugs et améliorations mineures',
                'categories' => [
                    'Améliorations' => [
                        'Optimisation des requêtes de base de données',
                        'Amélioration de la gestion des sessions',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de timezone',
                        'Correction des erreurs de validation des licences',
                        'Correction des problèmes d\'affichage dans l\'interface',
                    ],
                ],
            ],
            [
                'version' => '3.3.0',
                'date' => '09/04/2025',
                'description' => 'Amélioration du système de gestion des licences',
                'categories' => [
                    'Ajouts' => [
                        'Support des licences à durée limitée',
                        'API v2 avec support des webhooks',
                        'Système de notifications en temps réel',
                    ],
                    'Améliorations' => [
                        'Refonte de l\'API pour de meilleures performances',
                        'Amélioration du système de validation des licences',
                        'Interface d\'administration plus réactive',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de validation des licences expirées',
                        'Résolution des problèmes de performance de l\'API',
                    ],
                ],
            ],
            [
                'version' => '3.2.0',
                'date' => '08/04/2025',
                'description' => 'Amélioration du système de gestion des projets',
                'categories' => [
                    'Ajouts' => [
                        'Système de gestion des projets amélioré',
                        'Interface de gestion des équipes',
                        'Système de permissions avancé',
                    ],
                    'Améliorations' => [
                        'Refonte de l\'interface utilisateur',
                        'Optimisation des performances',
                        'Système de recherche plus puissant',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de pagination',
                        'Résolution des erreurs de validation',
                    ],
                ],
            ],
            [
                'version' => '3.1.0',
                'date' => '09/04/2025',
                'description' => 'Amélioration du système de gestion des licences',
                'categories' => [
                    'Ajouts' => [
                        'Support multi-projets',
                        'Interface de gestion des clés API améliorée',
                        'Système de journalisation des actions étendu',
                        'Support multilingue étendu (FR, EN, ZH, TR)',
                    ],
                    'Améliorations' => [
                        'Refonte de l\'interface utilisateur',
                        'Optimisation des performances de l\'API',
                        'Amélioration de la sécurité avec 2FA',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de validation des licences',
                        'Résolution des problèmes de cache',
                        'Amélioration de la gestion des timeouts',
                    ],
                ],
            ],
            [
                'version' => '3.0.0',
                'date' => '08/04/2025',
                'description' => 'Ajout du système de gestion des administrateurs et réinitialisation de mot de passe',
                'categories' => [
                    'Ajouts' => [
                        'Système de réinitialisation de mot de passe sécurisé',
                        'Gestion des tokens de réinitialisation',
                        'Interface d\'administration des comptes',
                        'Système de gestion des emails avec support multi-fournisseurs',
                        'Système de templates d\'emails avec variables dynamiques',
                        'Interface de gestion des variables d\'email',
                        'Support multilingue pour les templates',
                        'Configuration avancée des fournisseurs d\'email (PHPMail, Mailgun, Mailchimp, Rapidmail)',
                    ],
                    'Améliorations' => [
                        'Sécurité renforcée pour la gestion des mots de passe',
                        'Optimisation de la base de données',
                        'Refonte du menu de navigation avec nouvelle section Email',
                        'Design plus moderne et cohérent',
                        'Meilleure ergonomie des formulaires',
                        'Optimisation des performances',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de routage pour les sections email',
                        'Amélioration de la gestion des erreurs',
                        'Optimisation des performances de chargement',
                    ],
                ],
            ],
            [
                'version' => '2.0.0',
                'date' => '20/03/2024',
                'description' => 'Refonte majeure du système de gestion des emails',
                'categories' => [
                    'Ajouts' => [
                        'Nouveau système de gestion des emails avec support multi-fournisseurs',
                        'Système de templates d\'emails avec variables dynamiques',
                        'Interface de gestion des variables d\'email',
                        'Support multilingue pour les templates',
                        'Configuration avancée des fournisseurs d\'email (PHPMail, Mailgun, Mailchimp, Rapidmail)',
                    ],
                    'Améliorations' => [
                        'Refonte du menu de navigation avec nouvelle section Email',
                        'Design plus moderne et cohérent',
                        'Meilleure ergonomie des formulaires',
                        'Optimisation des performances',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de routage pour les sections email',
                        'Amélioration de la gestion des erreurs',
                        'Optimisation des performances de chargement',
                    ],
                ],
            ],
            [
                'version' => '1.9.5',
                'date' => '06/04/2025',
                'description' => 'Nouvelles fonctionnalités et améliorations majeures',
                'categories' => [
                    'Ajouts' => [
                        'Intégration de l\'authentification à double facteur Google',
                        'Support multilingue complet (EN, FR, ZH, TR)',
                        'Nouveau système de documentation intégré avec DocumentationController',
                        'Documentation autonome accessible via /public/api-docs.php',
                    ],
                    'Améliorations' => [
                        'Migration vers Laravel 12',
                        'Optimisation du système de routage API',
                        'Interface de documentation plus intuitive',
                        'Amélioration de la sécurité avec 2FA',
                    ],
                    'Corrections de bugs' => [
                        'Correction des commandes artisan pour Laravel 12',
                        'Résolution du problème "Target class [files]"',
                        'Correction du problème package:discover',
                        'Mise en place d\'une alternative à artisan serve avec serve.php',
                    ],
                ],
            ],
            [
                'version' => '1.9.4',
                'date' => '05/04/2025',
                'description' => 'Amélioration de la gestion des licences et de l\'interface',
                'categories' => [
                    'Ajouts' => [
                        'Système de notifications par email',
                        'Interface de gestion des clés API',
                        'Système de journalisation des actions',
                        'Support multilingue (FR, EN)',
                    ],
                    'Améliorations' => [
                        'Refonte de l\'interface utilisateur',
                        'Optimisation des performances',
                        'Amélioration de la sécurité',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de validation des licences',
                        'Résolution des problèmes de cache',
                        'Correction des erreurs d\'affichage',
                    ],
                ],
            ],
            [
                'version' => '1.9.3',
                'date' => '04/04/2025',
                'description' => 'Amélioration de la gestion des projets et de la sécurité',
                'categories' => [
                    'Ajouts' => [
                        'Système de gestion des projets',
                        'Interface de configuration des emails',
                        'Système de backup automatique',
                    ],
                    'Améliorations' => [
                        'Optimisation de la base de données',
                        'Amélioration de l\'API',
                        'Interface responsive',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de timezone',
                        'Résolution des conflits de routes',
                        'Correction des erreurs de validation',
                    ],
                ],
            ],
            [
                'version' => '1.9.2',
                'date' => '03/04/2025',
                'description' => 'Amélioration du système de licences et de l\'API',
                'categories' => [
                    'Ajouts' => [
                        'Système de gestion des licences',
                        'Interface d\'administration',
                        'API de vérification des licences',
                    ],
                    'Améliorations' => [
                        'Optimisation du code',
                        'Amélioration de la sécurité',
                        'Interface utilisateur améliorée',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de cache',
                        'Résolution des erreurs de validation',
                        'Correction des problèmes d\'affichage',
                    ],
                ],
            ],
            [
                'version' => '1.9.1',
                'date' => '02/04/2025',
                'description' => 'Amélioration de la gestion des utilisateurs et de la sécurité',
                'categories' => [
                    'Ajouts' => [
                        'Système de gestion des utilisateurs',
                        'Interface de configuration',
                        'Système de logs',
                    ],
                    'Améliorations' => [
                        'Optimisation des performances',
                        'Amélioration de la sécurité',
                        'Interface responsive',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de timezone',
                        'Résolution des conflits de routes',
                        'Correction des erreurs de validation',
                    ],
                ],
            ],
            [
                'version' => '1.9.0',
                'date' => '01/04/2025',
                'description' => 'Refonte majeure du système de gestion de licences',
                'categories' => [
                    'Ajouts' => [
                        'Nouveau système de gestion des licences',
                        'Interface d\'administration complètement repensée',
                        'API RESTful pour la vérification des licences',
                        'Documentation Swagger pour l\'API',
                    ],
                    'Améliorations' => [
                        'Optimisation des performances',
                        'Amélioration de la sécurité',
                        'Interface utilisateur plus intuitive',
                        'Documentation améliorée',
                    ],
                    'Corrections de bugs' => [
                        'Correction des problèmes de routage',
                        'Résolution des erreurs d\'authentification',
                        'Correction des problèmes d\'affichage',
                        'Amélioration de la gestion des erreurs',
                    ],
                ],
            ],
            [
                'version' => '1.8.0',
                'date' => '05/04/2025',
                'description' => 'Amélioration de l\'API et des points d\'accès pour la validation des licences',
                'categories' => [
                    'Ajouts' => [
                        'Création de points d\'entrée API directs pour la vérification des licences',
                        'Ajout d\'une route API de test pour vérifier que l\'API fonctionne correctement',
                        'Support des chemins d\'API avec et sans préfixe v1 pour la compatibilité',
                        'Ajout de logs pour le débogage des appels API',
                    ],
                    'Améliorations' => [
                        'Amélioration de la gestion des erreurs dans l\'API',
                        'Mise à jour de la documentation pour l\'intégration de l\'API dans les applications clientes',
                        'Création d\'un outil de test pour vérifier le bon fonctionnement de l\'API',
                    ],
                ],
            ],
            [
                'version' => '1.7.0',
                'date' => '05/04/2025',
                'description' => 'Amélioration majeure de la gestion des clés de licence',
                'categories' => [
                    'Ajouts' => [
                        'Système de recherche avancé pour les clés de licence (recherche par clé, domaine, IP, projet)',
                        'Filtres pour afficher les clés par projet, statut, domaine et adresse IP',
                        'Ajout du statut "Expirée" dans les filtres de clés de licence',
                        'Possibilité de générer jusqu\'a 100 000 clés de licence en une seule fois',
                        'Sélecteur de pagination amélioré avec options 10, 25, 50, 100, 500 et 1000 clés par page',
                    ],
                    'Améliorations' => [
                        'Réorganisation de l\'interface de gestion des clés pour une meilleure ergonomie',
                        'Amélioration du calcul des "clés utilisées" sur le tableau de bord',
                    ],
                    'Corrections de bugs' => [
                        'Correction de l\'affichage des icônes de pagination qui débordaient de l\'écran',
                    ],
                ],
            ],
            [
                'version' => '1.1.5',
                'date' => '01/04/2025',
                'description' => 'Amélioration de l\'interface utilisateur',
                'categories' => [
                    'Améliorations' => [
                        'Optimisation de l\'affichage des listes',
                    ],
                    'Corrections de bugs' => [
                        'Suppression des icônes de pagination',
                        'Correction de bugs mineurs',
                    ],
                ],
            ],
            [
                'version' => '1.1.0',
                'date' => '15/05/2025',
                'description' => 'Ajout de nouvelles fonctionnalités',
                'categories' => [
                    'Ajouts' => [
                        'Système de notifications par email',
                        'Ajout de statistiques avancées',
                    ],
                    'Améliorations' => [
                        'Amélioration du tableau de bord',
                    ],
                ],
            ],
            [
                'version' => '1.0.0',
                'date' => '01/05/2025',
                'description' => 'Version initiale de l\'application',
                'categories' => [
                    'Ajouts' => [
                        'Mise en place du système de gestion de licences',
                        'Interface d\'administration',
                        'API pour la vérification des licences',
                    ],
                ],
            ],
        ];
        
        return view('admin.version.index', compact('version', 'history'));
    }
}