<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AdminLicence') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <!-- Styles compilés via Vite -->
    <style>
        /* Styles de base en cas de problème avec TailwindCSS */
        body { font-family: system-ui, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .flex-1 { flex: 1; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-6 { gap: 1.5rem; }
        .rounded { border-radius: 0.25rem; }
        .bg-white { background-color: white; }
        .bg-blue-600 { background-color: #2563eb; }
        .hover\:bg-blue-700:hover { background-color: #1d4ed8; }
        .text-white { color: white; }
        .shadow { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
        .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .ml-4 { margin-left: 1rem; }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .text-2xl { font-size: 1.5rem; }
        .text-gray-900 { color: #111827; }
        .hover\:text-blue-600:hover { color: #2563eb; }
        .min-h-screen { min-height: 100vh; }
    </style>
    <!-- Utiliser les assets compilés via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">
    @php
        use Illuminate\Support\Facades\App;
    @endphp
    
    <header class="bg-white shadow">
        <div class="container mx-auto flex justify-between items-center py-4 px-6">
            <a href="/" class="text-2xl font-bold text-blue-700">AdminLicence</a>
            <nav class="flex gap-6 items-center">
                <a href="{{ route('frontend.home') }}" class="hover:text-blue-600 font-medium">{{ __('menu.home') }}</a>
                <a href="{{ route('frontend.features') }}" class="hover:text-blue-600 font-medium">{{ __('menu.features') }}</a>
                @if(config('app.show_pricing', true))
                <a href="{{ route('frontend.pricing') }}" class="hover:text-blue-600 font-medium">{{ __('menu.pricing') }}</a>
                @endif
                <a href="{{ route('frontend.faq') }}" class="hover:text-blue-600 font-medium">{{ __('menu.faq') }}</a>
                <a href="{{ route('frontend.support') }}" class="hover:text-blue-600 font-medium">{{ __('menu.support') }}</a>
                <a href="{{ route('direct.admin.login') }}" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('menu.login') }}</a>
                <form method="POST" action="{{ route('frontend.set.locale') }}" class="inline-block ml-4" id="localeForm-{{ rand() }}">
                    @csrf
                    <select name="locale" onchange="this.form.submit()" class="border rounded px-2 py-1">
                        <option value="fr" {{ App::getLocale() == 'fr' ? 'selected' : '' }}>Français</option>
                        <option value="en" {{ App::getLocale() == 'en' ? 'selected' : '' }}>English</option>
                        <option value="es" {{ App::getLocale() == 'es' ? 'selected' : '' }}>Español</option>
                        <option value="ru" {{ App::getLocale() == 'ru' ? 'selected' : '' }}>Русский</option>
                        <option value="de" {{ App::getLocale() == 'de' ? 'selected' : '' }}>Deutsch</option>
                        <option value="it" {{ App::getLocale() == 'it' ? 'selected' : '' }}>Italiano</option>
                        <option value="nl" {{ App::getLocale() == 'nl' ? 'selected' : '' }}>Nederlands</option>
                        <option value="pt" {{ App::getLocale() == 'pt' ? 'selected' : '' }}>Português</option>
                    </select>
                </form>
            </nav>
        </div>
    </header>
    <main class="flex-1">
        @yield('content')
    </main>
    <footer class="bg-gray-100 text-center py-6 mt-8 text-sm text-gray-500">
        &copy; {{ date('Y') }} AdminLicence. {{ __('footer.rights') }}
    </footer>
    <!-- Script pour charger les traductions si nécessaire -->
    <script>
        // Fonction pour charger les traductions depuis un fichier JSON
        function loadTranslations(locale) {
            fetch('/lang-' + locale + '.json')
                .then(response => response.json())
                .then(data => {
                    window.translations = data;
                })
                .catch(error => console.error('Erreur lors du chargement des traductions:', error));
        }
        
        // Charger les traductions de la langue actuelle
        loadTranslations('{{ app()->getLocale() }}');
    </script>
</body>
</html> 