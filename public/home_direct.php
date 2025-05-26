<?php
// Inclure les fichiers nécessaires pour l'affichage de la page d'accueil
require_once __DIR__ . '/../vendor/autoload.php';

// Initialiser l'application Laravel pour avoir accès aux fonctions de base
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Récupérer la locale depuis la session ou utiliser la locale par défaut
session_start();
$locale = $_SESSION['locale'] ?? 'fr';

// Fonction pour traduire les textes
function __($key) {
    global $locale;
    $translations = [
        'fr' => [
            'menu.home' => 'Accueil',
            'menu.features' => 'Fonctionnalités',
            'menu.pricing' => 'Tarifs',
            'menu.faq' => 'FAQ',
            'menu.support' => 'Support',
            'menu.login' => 'Connexion',
            'home.welcome' => 'Bienvenue sur AdminLicence',
            'home.tagline' => 'gérer vos licences efficacement',
            'home.feature1' => 'Gestion sécurisée de vos licences',
            'home.feature2' => 'Suivi et analyse de l\'utilisation',
            'home.feature3' => 'et plus encore',
        ],
        'en' => [
            'menu.home' => 'Home',
            'menu.features' => 'Features',
            'menu.pricing' => 'Pricing',
            'menu.faq' => 'FAQ',
            'menu.support' => 'Support',
            'menu.login' => 'Login',
            'home.welcome' => 'Welcome to AdminLicence',
            'home.tagline' => 'manage your licenses efficiently',
            'home.feature1' => 'Secure license management',
            'home.feature2' => 'Usage tracking and analysis',
            'home.feature3' => 'and more',
        ],
    ];
    
    return $translations[$locale][$key] ?? $key;
}

// Fonction pour générer une URL
function url($path) {
    return $path;
}

// Fonction pour générer une URL de route
function route($name) {
    $routes = [
        'home' => '/',
        'features' => '/features',
        'pricing' => '/pricing',
        'faq' => '/faq',
        'support' => '/support',
        'direct.admin.login' => '/admin/login',
        'set.locale' => '/set-locale',
    ];
    
    return $routes[$name] ?? '/';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $locale; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdminLicence - <?php echo __('menu.home'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="bg-white shadow">
        <div class="container mx-auto flex justify-between items-center py-4 px-6">
            <a href="/" class="text-2xl font-bold text-blue-700">AdminLicence</a>
            <nav class="flex gap-6 items-center">
                <a href="<?php echo route('home'); ?>" class="hover:text-blue-600 font-medium"><?php echo __('menu.home'); ?></a>
                <a href="<?php echo route('features'); ?>" class="hover:text-blue-600 font-medium"><?php echo __('menu.features'); ?></a>
                <a href="<?php echo route('pricing'); ?>" class="hover:text-blue-600 font-medium"><?php echo __('menu.pricing'); ?></a>
                <a href="<?php echo route('faq'); ?>" class="hover:text-blue-600 font-medium"><?php echo __('menu.faq'); ?></a>
                <a href="<?php echo route('support'); ?>" class="hover:text-blue-600 font-medium"><?php echo __('menu.support'); ?></a>
                <a href="<?php echo route('direct.admin.login'); ?>" class="ml-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?php echo __('menu.login'); ?></a>
                <form method="POST" action="<?php echo route('set.locale'); ?>" class="inline-block ml-4" id="localeForm">
                    <input type="hidden" name="_token" value="<?php echo bin2hex(random_bytes(32)); ?>">
                    <select name="locale" onchange="this.form.submit()" class="border rounded px-2 py-1">
                        <option value="fr" <?php echo $locale == 'fr' ? 'selected' : ''; ?>>Français</option>
                        <option value="en" <?php echo $locale == 'en' ? 'selected' : ''; ?>>English</option>
                    </select>
                </form>
            </nav>
        </div>
    </header>

    <main class="container mx-auto py-8 px-6">
        <div class="flex flex-wrap">
            <div class="w-full md:w-1/2 p-6 bg-blue-600 text-white rounded-lg">
                <h1 class="text-4xl font-bold mb-2"><?php echo __('home.welcome'); ?></h1>
                <p class="text-xl mb-8"><?php echo __('home.tagline'); ?></p>
                
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <div><?php echo __('home.feature1'); ?></div>
                    </div>
                    <div class="flex items-center">
                        <div class="mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div><?php echo __('home.feature2'); ?></div>
                    </div>
                    <div class="flex items-center">
                        <div class="mr-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div><?php echo __('home.feature3'); ?></div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 p-6">
                <div class="bg-white p-8 rounded-lg shadow">
                    <h2 class="text-2xl font-bold mb-6">Connexion</h2>
                    <form action="/admin/login" method="POST">
                        <input type="hidden" name="_token" value="<?php echo bin2hex(random_bytes(32)); ?>">
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 mb-2">Adresse e-mail</label>
                            <input type="email" id="email" name="email" class="w-full px-3 py-2 border rounded" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="block text-gray-700 mb-2">Mot de passe</label>
                            <input type="password" id="password" name="password" class="w-full px-3 py-2 border rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" class="mr-2">
                                <span>Se souvenir de moi</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700">Se connecter</button>
                        <div class="mt-4 text-center">
                            <a href="/admin/password/reset" class="text-blue-600 hover:underline">Mot de passe oublié ?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto px-6">
            <div class="flex flex-wrap">
                <div class="w-full md:w-1/3 mb-6 md:mb-0">
                    <h3 class="text-xl font-bold mb-4">AdminLicence</h3>
                    <p>Solution complète pour la gestion de vos licences logicielles.</p>
                </div>
                <div class="w-full md:w-1/3 mb-6 md:mb-0">
                    <h3 class="text-xl font-bold mb-4">Liens rapides</h3>
                    <ul class="space-y-2">
                        <li><a href="/" class="hover:text-blue-400">Accueil</a></li>
                        <li><a href="/features" class="hover:text-blue-400">Fonctionnalités</a></li>
                        <li><a href="/pricing" class="hover:text-blue-400">Tarifs</a></li>
                        <li><a href="/faq" class="hover:text-blue-400">FAQ</a></li>
                    </ul>
                </div>
                <div class="w-full md:w-1/3">
                    <h3 class="text-xl font-bold mb-4">Légal</h3>
                    <ul class="space-y-2">
                        <li><a href="/cgv" class="hover:text-blue-400">CGV</a></li>
                        <li><a href="/privacy" class="hover:text-blue-400">Politique de confidentialité</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p>&copy; <?php echo date('Y'); ?> AdminLicence. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
</body>
</html>
