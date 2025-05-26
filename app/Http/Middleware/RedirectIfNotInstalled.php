<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class RedirectIfNotInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Vérifier si l'application est déjà installée
            if ($this->isInstalled()) {
                return $next($request);
            }

            // Vérifier si la requête n'est pas déjà pour une route d'installation
            if (!$request->is('install*') && !$request->is('install.php') && !$request->is('public/install*')) {
                // Déterminer le chemin d'installation
                $installPath = '/install/install.php';
                
                // Rediriger vers l'installation avec code 302 (temporaire) au lieu de 301 (permanent)
                return redirect($installPath, 302);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Erreur dans RedirectIfNotInstalled: ' . $e->getMessage());
            // Retourner une réponse JSON pour les requêtes API
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Erreur serveur'], 500);
            }
            return response()->view('errors.500', [], 500);
        }
    }

    /**
     * Vérifier si l'application est installée
     *
     * @return bool
     */
    private function isInstalled()
    {
        try {
            // Vérifier si le fichier .env existe
            if (!file_exists(base_path('.env'))) {
                return false;
            }

            // Vérifier si le fichier .env est lisible
            if (!is_readable(base_path('.env'))) {
                return false;
            }

            // Lire le contenu du fichier .env
            $envContent = file_get_contents(base_path('.env'));
            if ($envContent === false) {
                return false;
            }

            // Vérifier si l'application est marquée comme installée
            if (strpos($envContent, 'APP_INSTALLED=true') === false) {
                return false;
            }

            // Vérifier la connexion à la base de données
            try {
                $pdo = new \PDO(
                    "mysql:host=" . env('DB_HOST') . ";port=" . env('DB_PORT') . ";dbname=" . env('DB_DATABASE'),
                    env('DB_USERNAME'),
                    env('DB_PASSWORD')
                );
                
                // Vérifier si les tables essentielles existent
                $tables = ['admins', 'clients', 'mail_configs'];
                foreach ($tables as $table) {
                    $result = $pdo->query("SHOW TABLES LIKE '$table'");
                    if ($result->rowCount() === 0) {
                        return false;
                    }
                }
                
                return true;
            } catch (\PDOException $e) {
                Log::error('Erreur de connexion à la base de données: ' . $e->getMessage());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification de l\'installation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Détecter si l'application est sur cPanel
     *
     * @return bool
     */
    private function isCpanel()
    {
        $cpanelIndicators = [
            '/home',
            '/public_html',
            'cpanel',
            '.cpanel',
            'cgi-bin'
        ];

        $serverPath = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        foreach ($cpanelIndicators as $indicator) {
            if (str_contains($serverPath, $indicator) || str_contains($requestUri, $indicator)) {
                return true;
            }
        }

        return false;
    }
}