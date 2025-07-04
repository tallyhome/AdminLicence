<!DOCTYPE html>
@php
use Illuminate\Support\Facades\Auth;
@endphp

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AdminLicence') }} - @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link href="{{ asset('vendor/fonts/figtree/figtree.css') }}" rel="stylesheet" />

    <!-- Flag Icons -->
    <link href="{{ asset('vendor/flag-icon-css/flag-icons.min.css') }}" rel="stylesheet">

    <!-- Font Awesome (version mise à jour pour résoudre les problèmes de glyphes) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Les traductions sont maintenant chargées dynamiquement via les routes Laravel -->
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/custom-pagination.css') }}">
    
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="{{ asset('css/dark-mode.css') }}">
</head>
<body class="font-sans antialiased {{ session('dark_mode', false) ? 'dark-mode' : '' }}">
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('admin.dashboard') }}" class="font-bold text-xl text-indigo-600">
                                AdminLicence
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.dashboard') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                {{ t('common.dashboard') }}
                            </a>
                            <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.projects.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                {{ t('common.projects') }}
                            </a>
                            <a href="{{ route('admin.serial-keys.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.serial-keys.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                {{ t('common.serial_keys') }}
                            </a>
                            <a href="{{ route('admin.client-example') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.client-example') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                {{ t('common.api_documentation') }}
                            </a>
                            <a href="{{ route('admin.mail.settings') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.mail.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                {{ t('common.email') }}
                            </a>
                            <a href="{{ route('admin.translations.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.translations.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out">
                                {{ t('common.translations') }}
                            </a>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <!-- Language Selector -->
                        <div class="mr-4 relative" x-data="{ open: false }">
                            <div>
                                <button @click="open = !open" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                    @php
                                    $currentLocale = app()->getLocale();
                                    $countryCode = $currentLocale;
                                    // Mappings spécifiques pour certaines langues
                                    if ($currentLocale === 'en') $countryCode = 'gb';
                                    if ($currentLocale === 'zh') $countryCode = 'cn';
                                    if ($currentLocale === 'ja') $countryCode = 'jp';
                                    if ($currentLocale === 'ar') $countryCode = 'sa';
                                    @endphp
                                    <span class="flag-icon flag-icon-{{ $countryCode }} mr-2"></span>
                                    <span class="text-gray-700">{{ strtoupper($currentLocale) }}</span>
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg">
                                <div class="py-1 rounded-md bg-white shadow-xs">
                                    @foreach(get_available_locales() as $locale)
                                    <form action="{{ route('admin.set.language') }}" method="POST" class="block">
                                        @csrf
                                        <input type="hidden" name="locale" value="{{ $locale }}">
                                        @php
                                        $countryCode = $locale;
                                        // Mappings spécifiques pour certaines langues
                                        if ($locale === 'en') $countryCode = 'gb';
                                        if ($locale === 'zh') $countryCode = 'cn';
                                        if ($locale === 'ja') $countryCode = 'jp';
                                        if ($locale === 'ar') $countryCode = 'sa';
                                        @endphp
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out {{ app()->getLocale() == $locale ? 'bg-gray-100' : '' }}">
                                            <span class="flag-icon flag-icon-{{ $countryCode }} mr-2"></span>
                                            {{ t('language.'.$locale) }}
                                        </button>
                                    </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="ml-3 relative" x-data="{ open: false }">
                            <div>
                                <button @click="open = !open" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg">
                                <div class="py-1 rounded-md bg-white shadow-xs">
                                    <form method="POST" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                            {{ t('common.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" x-data="{ open: false }">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-semibold text-gray-800">@yield('header')</h1>
            </div>
        </header>

        <!-- Flash Messages - Désactivé pour éviter les doublons avec les pages individuelles -->
        {{-- Les messages flash sont gérés individuellement par chaque page --}}
        
        <script>
            // Faire disparaître automatiquement les alertes après 5 secondes
            document.addEventListener('DOMContentLoaded', function() {
                const alerts = document.querySelectorAll('.alert-auto-dismiss');
                alerts.forEach(function(alert) {
                    setTimeout(function() {
                        alert.style.transition = 'opacity 1s';
                        alert.style.opacity = '0';
                        setTimeout(function() {
                            alert.style.display = 'none';
                        }, 1000);
                    }, 5000);
                });
            });
        </script>

        <!-- Licence Mode Indicator -->
        @include('admin.partials.licence-mode-indicator')
        
        <!-- Page Content -->
        <main>
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    @yield('content')
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="py-4 mt-auto admin-footer {{ request()->cookie('dark_mode') ? 'bg-gray-800 border-t border-gray-700 text-white' : 'bg-white border-t border-gray-100' }}">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <div>
                        &copy; {{ date('Y') }} {{ config('app.name') }}
                    </div>
                    <div>
                        <a href="{{ route('admin.version') }}" class="{{ request()->cookie('dark_mode') ? 'text-blue-400 hover:text-blue-300' : 'text-gray-600 hover:text-gray-900' }}">
                            Version {{ config('version.full')() }}
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- Dark Mode Script -->
    <script src="{{ asset('js/dark-mode.js') }}"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.0/dist/cdn.min.js"></script>
    
    <!-- ClipboardJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM chargé, initialisation des menus...');
            
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
                        // Créer une instance de collapse Bootstrap
                        var collapse = new bootstrap.Collapse(targetEl, {
                            toggle: false
                        });
                        
                        // Ajouter un gestionnaire d'événements pour permettre de fermer le collapse en cliquant à nouveau
                        collapseToggle.addEventListener('click', function(e) {
                            e.preventDefault();
                            // Toggle le collapse manuellement
                            if (targetEl.classList.contains('show')) {
                                collapse.hide();
                            } else {
                                collapse.show();
                            }
                        });
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
</body>
</html>