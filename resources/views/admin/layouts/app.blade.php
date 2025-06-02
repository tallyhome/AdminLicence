@php use Illuminate\Support\Facades\Auth; @endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link href="{{ asset('vendor/fonts/figtree/figtree.css') }}" rel="stylesheet" />

    <!-- Flag Icons -->
    <link href="{{ asset('vendor/flag-icon-css/flag-icons.min.css') }}" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        .sidebar .nav-link:hover {
            color: rgba(255,255,255,1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .content {
            padding: 20px;
            padding-bottom: 80px; /* Espace pour le footer */
        }
        .main-content-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
            position: relative;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 250px; /* Largeur du menu de gauche */
            right: 0;
            background-color: #fff;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            z-index: 1000;
        }
        /* Styles pour le sélecteur de langue */
        .navbar .nav-item.dropdown .nav-link {
            color: #333 !important;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        /* Ajout du décalage pour le sélecteur de langue */
        .navbar .nav-item:last-child {
            margin-left: 50px;
        }
        .navbar .dropdown-menu {
            min-width: 120px;
            max-width: 120px;
            padding: 0.25rem 0;
        }
        .navbar .dropdown-item {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        .navbar .dropdown-item.active {
            background-color: #f8f9fa;
            color: #333;
        }
        .navbar .dropdown-item:hover {
            background-color: #e9ecef;
        }
        /* Styles pour les drapeaux */
        /* Les styles des drapeaux sont maintenant gérés via Vite */
        /* Styles spécifiques pour le sélecteur de langue */
        .navbar .language-selector.dropdown .nav-link {
            color: #333 !important;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        .navbar .language-selector .dropdown-menu {
            min-width: 120px;
            max-width: 120px;
            padding: 0.25rem 0;
        }
        .navbar .language-selector .dropdown-item {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            color: #333;
        }
        .navbar .language-selector .dropdown-item.active {
            background-color: #f8f9fa;
            color: #333;
        }
        .navbar .language-selector .dropdown-item:hover {
            background-color: #e9ecef;
        }

        /* Styles spécifiques pour les notifications */
        #notification-list.dropdown-menu {
            width: 400px !important;
            max-height: 600px !important;
            min-width: 400px !important;
        }
    </style>
    @stack('styles')
</head>
<body class="{{ session('dark_mode') ? 'dark-mode' : '' }}">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3" style="width: 250px;">
            <div class="mb-4">
                <h4>{{ config('app.name', 'Laravel') }}</h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i> {{ t('common.dashboard') }}
                    </a>
                </li>

                <!-- Gestion des licences -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}" href="{{ route('admin.projects.index') }}">
                        <i class="fas fa-project-diagram me-2"></i> {{ t('common.projects') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.serial-keys.*') ? 'active' : '' }}" href="{{ route('admin.serial-keys.index') }}">
                        <i class="fas fa-key me-2"></i> {{ t('common.serial_keys') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.api-keys.*') ? 'active' : '' }}" href="{{ route('admin.api-keys.index') }}">
                        <i class="fas fa-code me-2"></i> {{ t('common.api_keys') }}
                    </a>
                </li>

                <!-- Gestion des emails -->
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#emailSubmenu">
                        <i class="fas fa-envelope me-2"></i>{{ t('common.email') }}
                    </a>
                    <div class="collapse" id="emailSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.mail.settings') }}">
                                    <i class="fas fa-cog me-2"></i>{{ t('common.settings') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.mail.providers.phpmail.index') }}">
                                    <i class="fas fa-mail-bulk me-2"></i>PHPMail
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.mail.providers.mailgun.index') }}">
                                    <i class="fas fa-mail-bulk me-2"></i>Mailgun
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.mail.providers.mailchimp.index') }}">
                                    <i class="fas fa-mail-bulk me-2"></i>Mailchimp
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.email.templates.index') }}">
                                    <i class="fas fa-file-alt me-2"></i>Templates
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Documentation -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#documentationSubmenu" role="button">
                        <i class="fas fa-book me-2"></i> {{ t('layout.documentation') }}
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.api.documentation') || request()->routeIs('admin.licence.documentation') ? 'show' : '' }}" id="documentationSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.api.documentation') ? 'active' : '' }}" href="{{ route('admin.api.documentation') }}">
                                    <i class="fas fa-code me-2"></i> Documentation API
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.licence.documentation') ? 'active' : '' }}" href="{{ route('admin.licence.documentation') }}">
                                    <i class="fas fa-key me-2"></i> Documentation des clés
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.email.documentation') ? 'active' : '' }}" href="{{ route('admin.email.documentation') }}">
                                    <i class="fas fa-envelope me-2"></i> Documentation des fournisseurs d'email
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.version') ? 'active' : '' }}" href="{{ route('admin.version') }}">
                        <i class="fas fa-code-branch me-2"></i> {{ t('layout.version_info') }}
                    </a>
                </li>

                <!-- Paramètres -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#settingsSubmenu" role="button">
                        <i class="fas fa-cog me-2"></i> {{ t('common.settings') }}
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.settings.*') ? 'show' : '' }}" id="settingsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                                    <i class="fas fa-sliders-h me-2"></i> Général
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.two-factor') ? 'active' : '' }}" href="{{ route('admin.settings.two-factor') }}">
                                    <i class="fas fa-shield-alt me-2"></i> 2FA
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.settings.translations.*') ? 'active' : '' }}" href="{{ route('admin.settings.translations.index') }}">
                                    <i class="fas fa-language me-2"></i> Langues
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="flex-grow-1 main-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <!-- Language Selector -->
                            <li class="nav-item">
                                @include('admin.layouts.partials.language-selector')
                            </li>
                            <!-- Composant de notifications -->
                            <li class="nav-item">
                                @include('admin.layouts.partials.notifications')
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <span>{{ Auth::guard('admin')->user()->name }}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li>
                                        <a href="{{ route('admin.profile.edit') }}" class="dropdown-item">
                                            <i class="fas fa-user-cog me-2"></i> Profil
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('admin.logout') }}" id="logout-form">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <div class="content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
            
            <!-- Footer -->
            @include('admin.layouts.partials.footer')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- ClipboardJS -->
    <script src="{{ asset('vendor/clipboardjs/clipboard.min.js') }}"></script>
    
    <!-- Alpine.js (chargé après Bootstrap pour éviter les conflits) -->
    <script defer src="{{ asset('vendor/alpinejs/alpine.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser tous les tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialiser tous les dropdowns manuellement
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
                // Créer une instance de dropdown Bootstrap avec autoClose: 'outside'
                // Cela fermera automatiquement le dropdown quand on clique sur un autre dropdown
                var dropdown = new bootstrap.Dropdown(dropdownToggle, {
                    autoClose: 'outside'
                });
                
                // Ajouter un gestionnaire d'événements pour le clic
                dropdownToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdown.toggle();
                });
            });
            
            // S'assurer que tous les boutons dropdown sont cliquables
            document.querySelectorAll('.dropdown-toggle').forEach(function(el) {
                el.style.cursor = 'pointer';
            });
            
            // Assurer que le menu des notifications a la bonne taille
            var notificationList = document.getElementById('notification-list');
            if (notificationList) {
                notificationList.style.width = '400px';
                notificationList.style.maxHeight = '600px';
                notificationList.style.overflowY = 'auto';
            }
            
            // Log pour débogage
            console.log('Menus dropdown initialisés');
            
            // Assurer que les collapsibles sont correctement cliquables
            document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(collapseToggle) {
                // S'assurer que l'élément est cliquable
                collapseToggle.style.cursor = 'pointer';
                
                // Identifier la cible du collapse
                var targetId = collapseToggle.getAttribute('data-bs-target') || collapseToggle.getAttribute('href');
                if (targetId) {
                    var targetEl = document.querySelector(targetId);
                    if (targetEl) {
                        // Supprimer les gestionnaires d'événements existants pour éviter les doublons
                        collapseToggle.removeEventListener('click', toggleCollapse);
                        
                        // Fonction pour basculer l'état du collapse
                        function toggleCollapse(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Utiliser l'API Bootstrap pour basculer l'état du collapse
                            var collapseInstance = bootstrap.Collapse.getInstance(targetEl);
                            if (collapseInstance) {
                                collapseInstance.toggle();
                            } else {
                                new bootstrap.Collapse(targetEl);
                            }
                        }
                        
                        // Ajouter le gestionnaire d'événements
                        collapseToggle.addEventListener('click', toggleCollapse);
                    }
                }
            });
            
            // Initialiser ClipboardJS
            var clipboard = new ClipboardJS('.copy-btn');
            clipboard.on('success', function(e) {
                var tooltip = bootstrap.Tooltip.getInstance(e.trigger);
                if (tooltip) {
                    tooltip.dispose();
                }
                
                var newTooltip = new bootstrap.Tooltip(e.trigger, {
                    title: 'Copié !',
                    placement: 'top',
                    trigger: 'manual'
                });
                
                newTooltip.show();
                
                setTimeout(function() {
                    newTooltip.dispose();
                }, 1000);
                
                e.clearSelection();
            });
            
            // Initialiser les tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    @stack('scripts')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Gestion des clics en dehors des menus
            document.addEventListener('click', function(event) {
                const target = event.target;
                // Si on clique en dehors d'un menu ou sous-menu
                if (!target.closest('.nav-link') && !target.closest('.collapse')) {
                    // Fermer tous les sous-menus
                    document.querySelectorAll('.collapse').forEach(collapse => {
                        if (collapse.classList.contains('show')) {
                            bootstrap.Collapse.getInstance(collapse).hide();
                        }
                    });
                }
            });

            // 2. Gestion des sous-menus
            document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Empêcher le comportement par défaut
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('data-bs-target') || this.getAttribute('href');
                    const targetCollapse = document.querySelector(targetId);
                    
                    // Fermer tous les autres sous-menus sauf celui qu'on veut ouvrir
                    document.querySelectorAll('.collapse.show').forEach(collapse => {
                        if (collapse !== targetCollapse) {
                            bootstrap.Collapse.getInstance(collapse).hide();
                        }
                    });
                });
            });
            
            // Gestion des menus déroulants du header (utilisateur, notifications)
            // Le menu de langue est géré dans son propre fichier
            
            // S'assurer que le menu utilisateur fonctionne correctement
            const userDropdown = document.getElementById('navbarDropdown');
            if (userDropdown) {
                const userDropdownInstance = new bootstrap.Dropdown(userDropdown, {
                    autoClose: true
                });
                
                userDropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userDropdownInstance.toggle();
                });
            }
            
            // Gestion du menu de notifications
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown) {
                const notificationDropdownInstance = new bootstrap.Dropdown(notificationDropdown, {
                    autoClose: true
                });
                
                notificationDropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    notificationDropdownInstance.toggle();
                });
            }
            
            // Fermer les autres menus lorsqu'un menu est ouvert
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
                dropdownToggle.addEventListener('show.bs.dropdown', function() {
                    // Fermer tous les autres menus déroulants
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(openMenu) {
                        if (!openMenu.previousElementSibling.isSameNode(dropdownToggle)) {
                            const dropdown = bootstrap.Dropdown.getInstance(openMenu.previousElementSibling);
                            if (dropdown) {
                                dropdown.hide();
                            }
                        }
                    });
                });
            });
            
            // S'assurer que le formulaire de déconnexion fonctionne correctement
            const logoutForm = document.getElementById('logout-form');
            if (logoutForm) {
                logoutForm.addEventListener('submit', function(e) {
                    console.log('Formulaire de déconnexion soumis');
                });
            }

            // Faire disparaître automatiquement les alertes après 5 secondes
            document.querySelectorAll('.alert').forEach(function(alert) {
                if (!alert.classList.contains('alert-persistent')) {
                    setTimeout(function() {
                        alert.style.transition = 'opacity 0.5s ease-out';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.remove();
                        }, 500);
                    }, 5000);
                }
            });
        });
    </script>
</body>
</html>