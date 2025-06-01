<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SecurityAudit extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * @var string
     */
    protected $signature = 'security:audit {--fix : Tenter de corriger automatiquement les problèmes détectés}';

    /**
     * La description de la commande console.
     *
     * @var string
     */
    protected $description = 'Effectue un audit de sécurité de l\'application et propose des corrections';

    /**
     * Exécuter la commande console.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Démarrage de l\'audit de sécurité d\'AdminLicence...');
        $this->newLine();

        $issues = [];
        $fixedIssues = [];

        // Vérifier le fichier .env
        $this->info('1. Vérification des variables d\'environnement...');
        $envIssues = $this->checkEnvironmentVariables();
        $issues = array_merge($issues, $envIssues);

        // Vérifier les middlewares de sécurité
        $this->info('2. Vérification des middlewares de sécurité...');
        $middlewareIssues = $this->checkSecurityMiddlewares();
        $issues = array_merge($issues, $middlewareIssues);

        // Vérifier les en-têtes HTTP
        $this->info('3. Vérification des en-têtes HTTP de sécurité...');
        $headerIssues = $this->checkSecurityHeaders();
        $issues = array_merge($issues, $headerIssues);

        // Vérifier le chiffrement des clés de licence
        $this->info('4. Vérification du chiffrement des clés de licence...');
        $encryptionIssues = $this->checkLicenceKeyEncryption();
        $issues = array_merge($issues, $encryptionIssues);

        // Vérifier la configuration HTTPS
        $this->info('5. Vérification de la configuration HTTPS...');
        $httpsIssues = $this->checkHttpsConfiguration();
        $issues = array_merge($issues, $httpsIssues);

        // Afficher les résultats
        $this->newLine();
        $this->info('Résultats de l\'audit de sécurité :');
        $this->newLine();

        if (count($issues) === 0) {
            $this->info('✅ Aucun problème de sécurité détecté !');
        } else {
            $this->warn('⚠️ ' . count($issues) . ' problème(s) de sécurité détecté(s) :');
            
            foreach ($issues as $index => $issue) {
                $this->newLine();
                $this->warn(($index + 1) . '. ' . $issue['title']);
                $this->line('   Description: ' . $issue['description']);
                $this->line('   Recommandation: ' . $issue['recommendation']);
                $this->line('   Sévérité: ' . $issue['severity']);
                
                // Tenter de corriger le problème si l'option --fix est utilisée
                if ($this->option('fix') && isset($issue['fix_function'])) {
                    $this->info('   Tentative de correction automatique...');
                    $fixResult = call_user_func([$this, $issue['fix_function']]);
                    
                    if ($fixResult) {
                        $this->info('   ✅ Problème corrigé avec succès !');
                        $fixedIssues[] = $issue;
                    } else {
                        $this->error('   ❌ Échec de la correction automatique. Correction manuelle requise.');
                    }
                }
            }
            
            // Afficher un résumé des corrections
            if ($this->option('fix')) {
                $this->newLine();
                $this->info('Résumé des corrections :');
                $this->info('- ' . count($fixedIssues) . ' problème(s) corrigé(s)');
                $this->info('- ' . (count($issues) - count($fixedIssues)) . ' problème(s) nécessitant une correction manuelle');
            }
            
            // Afficher les recommandations générales
            $this->newLine();
            $this->info('Recommandations générales :');
            $this->line('1. Effectuez régulièrement des audits de sécurité');
            $this->line('2. Maintenez les dépendances à jour avec composer update');
            $this->line('3. Utilisez HTTPS en production');
            $this->line('4. Surveillez les journaux d\'application pour détecter les activités suspectes');
            $this->line('5. Sauvegardez régulièrement la base de données et les fichiers de configuration');
        }

        return Command::SUCCESS;
    }

    /**
     * Vérifie les variables d'environnement liées à la sécurité
     *
     * @return array
     */
    private function checkEnvironmentVariables(): array
    {
        $issues = [];
        $envFile = base_path('.env');
        
        if (!File::exists($envFile)) {
            $issues[] = [
                'title' => 'Fichier .env manquant',
                'description' => 'Le fichier .env n\'existe pas à la racine du projet.',
                'recommendation' => 'Créez un fichier .env basé sur .env.example',
                'severity' => 'Critique',
                'fix_function' => 'fixEnvFile'
            ];
            return $issues;
        }
        
        $envContent = File::get($envFile);
        
        // Vérifier les variables de sécurité importantes
        $securityVars = [
            'APP_KEY' => 'Clé d\'application Laravel',
            'SECURITY_ENABLE_SSL_VERIFY' => 'Vérification SSL pour les requêtes API',
            'SECURITY_ENCRYPT_LICENCE_KEYS' => 'Chiffrement des clés de licence',
            'SECURITY_TOKEN_SECRET' => 'Secret pour la génération des tokens',
            'SECURITY_RATE_LIMIT_ATTEMPTS' => 'Limite de tentatives pour le rate limiting',
            'SECURITY_TOKEN_EXPIRY_MINUTES' => 'Durée d\'expiration des tokens'
        ];
        
        foreach ($securityVars as $var => $description) {
            if (!preg_match('/' . $var . '=/', $envContent)) {
                $issues[] = [
                    'title' => 'Variable d\'environnement manquante: ' . $var,
                    'description' => 'La variable ' . $var . ' (' . $description . ') n\'est pas définie dans le fichier .env',
                    'recommendation' => 'Ajoutez la variable ' . $var . ' dans le fichier .env en vous basant sur .env.example',
                    'severity' => 'Élevée',
                    'fix_function' => 'fixMissingEnvVar'
                ];
            }
        }
        
        // Vérifier si APP_KEY est vide
        if (preg_match('/APP_KEY=\s*$/', $envContent) || preg_match('/APP_KEY=""/', $envContent) || preg_match('/APP_KEY=\'\'/', $envContent)) {
            $issues[] = [
                'title' => 'APP_KEY non définie',
                'description' => 'La clé d\'application Laravel (APP_KEY) est vide.',
                'recommendation' => 'Générez une nouvelle clé avec la commande php artisan key:generate',
                'severity' => 'Critique',
                'fix_function' => 'fixAppKey'
            ];
        }
        
        return $issues;
    }

    /**
     * Vérifie la présence et la configuration des middlewares de sécurité
     *
     * @return array
     */
    private function checkSecurityMiddlewares(): array
    {
        $issues = [];
        $kernelPath = app_path('Http/Kernel.php');
        
        if (!File::exists($kernelPath)) {
            $issues[] = [
                'title' => 'Fichier Kernel.php manquant',
                'description' => 'Le fichier Http/Kernel.php n\'existe pas.',
                'recommendation' => 'Restaurez le fichier Kernel.php à partir d\'une sauvegarde ou réinstallez Laravel.',
                'severity' => 'Critique'
            ];
            return $issues;
        }
        
        $kernelContent = File::get($kernelPath);
        
        // Vérifier les middlewares de sécurité importants
        $securityMiddlewares = [
            'SecurityHeaders' => 'Middleware pour les en-têtes HTTP de sécurité',
            'ApiRateLimiter' => 'Middleware pour limiter les tentatives d\'API',
            'JwtAuthenticate' => 'Middleware pour l\'authentification JWT'
        ];
        
        foreach ($securityMiddlewares as $middleware => $description) {
            if (!str_contains($kernelContent, $middleware)) {
                $issues[] = [
                    'title' => 'Middleware de sécurité manquant: ' . $middleware,
                    'description' => 'Le middleware ' . $middleware . ' (' . $description . ') n\'est pas configuré dans Kernel.php',
                    'recommendation' => 'Ajoutez le middleware ' . $middleware . ' dans le fichier Kernel.php',
                    'severity' => 'Élevée'
                ];
            }
        }
        
        return $issues;
    }

    /**
     * Vérifie la configuration des en-têtes HTTP de sécurité
     *
     * @return array
     */
    private function checkSecurityHeaders(): array
    {
        $issues = [];
        $htaccessPath = public_path('.htaccess');
        
        if (!File::exists($htaccessPath)) {
            $issues[] = [
                'title' => 'Fichier .htaccess manquant',
                'description' => 'Le fichier .htaccess n\'existe pas dans le dossier public.',
                'recommendation' => 'Créez un fichier .htaccess avec les en-têtes de sécurité appropriés.',
                'severity' => 'Élevée',
                'fix_function' => 'fixHtaccess'
            ];
            return $issues;
        }
        
        $htaccessContent = File::get($htaccessPath);
        
        // Vérifier les en-têtes de sécurité importants
        $securityHeaders = [
            'X-XSS-Protection' => 'Protection contre les attaques XSS',
            'X-Content-Type-Options' => 'Protection contre le MIME-sniffing',
            'X-Frame-Options' => 'Protection contre le clickjacking',
            'Content-Security-Policy' => 'Politique de sécurité du contenu',
            'Strict-Transport-Security' => 'HSTS (HTTP Strict Transport Security)',
            'Referrer-Policy' => 'Politique de référent',
            'Permissions-Policy' => 'Politique de permissions'
        ];
        
        foreach ($securityHeaders as $header => $description) {
            if (!str_contains($htaccessContent, $header)) {
                $issues[] = [
                    'title' => 'En-tête HTTP de sécurité manquant: ' . $header,
                    'description' => 'L\'en-tête ' . $header . ' (' . $description . ') n\'est pas configuré dans .htaccess',
                    'recommendation' => 'Ajoutez l\'en-tête ' . $header . ' dans le fichier .htaccess',
                    'severity' => 'Moyenne',
                    'fix_function' => 'fixSecurityHeaders'
                ];
            }
        }
        
        return $issues;
    }

    /**
     * Vérifie la configuration du chiffrement des clés de licence
     *
     * @return array
     */
    private function checkLicenceKeyEncryption(): array
    {
        $issues = [];
        
        // Vérifier si la variable d'environnement est activée
        if (env('SECURITY_ENCRYPT_LICENCE_KEYS', false) === false) {
            $issues[] = [
                'title' => 'Chiffrement des clés de licence désactivé',
                'description' => 'Le chiffrement des clés de licence n\'est pas activé dans la configuration.',
                'recommendation' => 'Activez SECURITY_ENCRYPT_LICENCE_KEYS=true dans le fichier .env et exécutez php artisan licence:reencrypt-keys pour chiffrer les clés existantes.',
                'severity' => 'Élevée',
                'fix_function' => 'fixLicenceKeyEncryption'
            ];
        }
        
        // Vérifier si le service de chiffrement existe
        if (!class_exists('App\Services\EncryptionService')) {
            $issues[] = [
                'title' => 'Service de chiffrement manquant',
                'description' => 'Le service EncryptionService n\'existe pas dans l\'application.',
                'recommendation' => 'Créez le service EncryptionService pour gérer le chiffrement des données sensibles.',
                'severity' => 'Élevée'
            ];
        }
        
        return $issues;
    }

    /**
     * Vérifie la configuration HTTPS
     *
     * @return array
     */
    private function checkHttpsConfiguration(): array
    {
        $issues = [];
        
        // Vérifier si HTTPS est forcé en production
        if (env('APP_ENV') === 'production' && env('FORCE_HTTPS', false) === false) {
            $issues[] = [
                'title' => 'HTTPS non forcé en production',
                'description' => 'L\'application ne force pas l\'utilisation de HTTPS en environnement de production.',
                'recommendation' => 'Ajoutez FORCE_HTTPS=true dans le fichier .env en production et configurez le middleware approprié.',
                'severity' => 'Élevée',
                'fix_function' => 'fixHttpsConfiguration'
            ];
        }
        
        // Vérifier si la vérification SSL est activée pour les requêtes API
        if (env('SECURITY_ENABLE_SSL_VERIFY', false) === false) {
            $issues[] = [
                'title' => 'Vérification SSL désactivée pour les requêtes API',
                'description' => 'La vérification SSL est désactivée pour les requêtes API externes.',
                'recommendation' => 'Activez SECURITY_ENABLE_SSL_VERIFY=true dans le fichier .env pour sécuriser les communications API.',
                'severity' => 'Moyenne',
                'fix_function' => 'fixSslVerification'
            ];
        }
        
        return $issues;
    }

    /**
     * Corrige le fichier .env manquant
     *
     * @return bool
     */
    private function fixEnvFile(): bool
    {
        $envExamplePath = base_path('.env.example');
        $envPath = base_path('.env');
        
        if (!File::exists($envExamplePath)) {
            return false;
        }
        
        try {
            File::copy($envExamplePath, $envPath);
            $this->info('Fichier .env créé à partir de .env.example');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Corrige la clé d'application manquante
     *
     * @return bool
     */
    private function fixAppKey(): bool
    {
        try {
            Artisan::call('key:generate');
            $this->info('Nouvelle clé d\'application générée');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Corrige les variables d'environnement manquantes
     *
     * @return bool
     */
    private function fixMissingEnvVar(): bool
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');
        
        if (!File::exists($envPath) || !File::exists($envExamplePath)) {
            return false;
        }
        
        try {
            $envContent = File::get($envPath);
            $envExampleContent = File::get($envExamplePath);
            
            // Extraire les variables d'exemple
            preg_match_all('/^([A-Z0-9_]+)=(.*)$/m', $envExampleContent, $exampleMatches, PREG_SET_ORDER);
            
            $exampleVars = [];
            foreach ($exampleMatches as $match) {
                $exampleVars[$match[1]] = $match[2];
            }
            
            // Extraire les variables actuelles
            preg_match_all('/^([A-Z0-9_]+)=(.*)$/m', $envContent, $currentMatches, PREG_SET_ORDER);
            
            $currentVars = [];
            foreach ($currentMatches as $match) {
                $currentVars[$match[1]] = $match[2];
            }
            
            // Ajouter les variables manquantes
            $newEnvContent = $envContent;
            foreach ($exampleVars as $key => $value) {
                if (!isset($currentVars[$key]) && str_contains($key, 'SECURITY_')) {
                    $newEnvContent .= "\n{$key}={$value}";
                }
            }
            
            File::put($envPath, $newEnvContent);
            $this->info('Variables d\'environnement de sécurité ajoutées au fichier .env');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Corrige les en-têtes de sécurité HTTP manquants
     *
     * @return bool
     */
    private function fixSecurityHeaders(): bool
    {
        $htaccessPath = public_path('.htaccess');
        
        if (!File::exists($htaccessPath)) {
            return false;
        }
        
        try {
            $htaccessContent = File::get($htaccessPath);
            
            // Vérifier si la section des en-têtes existe déjà
            if (!str_contains($htaccessContent, '<IfModule mod_headers.c>')) {
                $securityHeaders = "\n# En-têtes de sécurité HTTP\n";
                $securityHeaders .= "<IfModule mod_headers.c>\n";
                $securityHeaders .= "    # Protection XSS\n";
                $securityHeaders .= "    Header set X-XSS-Protection \"1; mode=block\"\n";
                $securityHeaders .= "    \n";
                $securityHeaders .= "    # Empêcher le MIME-sniffing\n";
                $securityHeaders .= "    Header set X-Content-Type-Options \"nosniff\"\n";
                $securityHeaders .= "    \n";
                $securityHeaders .= "    # Protection contre le clickjacking\n";
                $securityHeaders .= "    Header set X-Frame-Options \"SAMEORIGIN\"\n";
                $securityHeaders .= "    \n";
                $securityHeaders .= "    # Politique de sécurité du contenu (CSP)\n";
                $securityHeaders .= "    Header set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self';\"\n";
                $securityHeaders .= "    \n";
                $securityHeaders .= "    # Strict Transport Security (HSTS)\n";
                $securityHeaders .= "    Header set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"\n";
                $securityHeaders .= "    \n";
                $securityHeaders .= "    # Référent Policy\n";
                $securityHeaders .= "    Header set Referrer-Policy \"strict-origin-when-cross-origin\"\n";
                $securityHeaders .= "    \n";
                $securityHeaders .= "    # Permissions Policy\n";
                $securityHeaders .= "    Header set Permissions-Policy \"camera=(), microphone=(), geolocation=()\"\n";
                $securityHeaders .= "</IfModule>\n";
                
                $htaccessContent .= $securityHeaders;
                File::put($htaccessPath, $htaccessContent);
                $this->info('En-têtes de sécurité HTTP ajoutés au fichier .htaccess');
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Corrige la configuration du chiffrement des clés de licence
     *
     * @return bool
     */
    private function fixLicenceKeyEncryption(): bool
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return false;
        }
        
        try {
            $envContent = File::get($envPath);
            
            // Activer le chiffrement des clés de licence
            if (str_contains($envContent, 'SECURITY_ENCRYPT_LICENCE_KEYS=false')) {
                $envContent = str_replace('SECURITY_ENCRYPT_LICENCE_KEYS=false', 'SECURITY_ENCRYPT_LICENCE_KEYS=true', $envContent);
                File::put($envPath, $envContent);
                $this->info('Chiffrement des clés de licence activé dans le fichier .env');
                
                // Exécuter la commande de rechiffrement
                $this->info('Exécution de la commande de rechiffrement des clés de licence...');
                Artisan::call('licence:reencrypt-keys', ['--force' => true]);
                $this->info(Artisan::output());
                
                return true;
            } elseif (!str_contains($envContent, 'SECURITY_ENCRYPT_LICENCE_KEYS')) {
                $envContent .= "\nSECURITY_ENCRYPT_LICENCE_KEYS=true\n";
                File::put($envPath, $envContent);
                $this->info('Variable SECURITY_ENCRYPT_LICENCE_KEYS ajoutée au fichier .env');
                
                // Exécuter la commande de rechiffrement
                $this->info('Exécution de la commande de rechiffrement des clés de licence...');
                Artisan::call('licence:reencrypt-keys', ['--force' => true]);
                $this->info(Artisan::output());
                
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Corrige la configuration HTTPS
     *
     * @return bool
     */
    private function fixHttpsConfiguration(): bool
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return false;
        }
        
        try {
            $envContent = File::get($envPath);
            
            // Activer HTTPS forcé
            if (str_contains($envContent, 'FORCE_HTTPS=false')) {
                $envContent = str_replace('FORCE_HTTPS=false', 'FORCE_HTTPS=true', $envContent);
                File::put($envPath, $envContent);
                $this->info('HTTPS forcé activé dans le fichier .env');
                return true;
            } elseif (!str_contains($envContent, 'FORCE_HTTPS')) {
                $envContent .= "\nFORCE_HTTPS=true\n";
                File::put($envPath, $envContent);
                $this->info('Variable FORCE_HTTPS ajoutée au fichier .env');
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Corrige la vérification SSL pour les requêtes API
     *
     * @return bool
     */
    private function fixSslVerification(): bool
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            return false;
        }
        
        try {
            $envContent = File::get($envPath);
            
            // Activer la vérification SSL
            if (str_contains($envContent, 'SECURITY_ENABLE_SSL_VERIFY=false')) {
                $envContent = str_replace('SECURITY_ENABLE_SSL_VERIFY=false', 'SECURITY_ENABLE_SSL_VERIFY=true', $envContent);
                File::put($envPath, $envContent);
                $this->info('Vérification SSL activée dans le fichier .env');
                return true;
            } elseif (!str_contains($envContent, 'SECURITY_ENABLE_SSL_VERIFY')) {
                $envContent .= "\nSECURITY_ENABLE_SSL_VERIFY=true\n";
                File::put($envPath, $envContent);
                $this->info('Variable SECURITY_ENABLE_SSL_VERIFY ajoutée au fichier .env');
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Corrige le fichier .htaccess manquant
     *
     * @return bool
     */
    private function fixHtaccess(): bool
    {
        $htaccessPath = public_path('.htaccess');
        
        try {
            $htaccessContent = "<IfModule mod_rewrite.c>\n";
            $htaccessContent .= "    <IfModule mod_negotiation.c>\n";
            $htaccessContent .= "        Options -MultiViews -Indexes\n";
            $htaccessContent .= "    </IfModule>\n\n";
            $htaccessContent .= "    RewriteEngine On\n\n";
            $htaccessContent .= "    # Handle Authorization Header\n";
            $htaccessContent .= "    RewriteCond %{HTTP:Authorization} .\n";
            $htaccessContent .= "    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n\n";
            $htaccessContent .= "    # Redirect Trailing Slashes If Not A Folder...\n";
            $htaccessContent .= "    RewriteCond %{REQUEST_FILENAME} !-d\n";
            $htaccessContent .= "    RewriteCond %{REQUEST_URI} (.+)/$\n";
            $htaccessContent .= "    RewriteRule ^ %1 [L,R=301]\n\n";
            $htaccessContent .= "    # Send Requests To Front Controller...\n";
            $htaccessContent .= "    RewriteCond %{REQUEST_FILENAME} !-d\n";
            $htaccessContent .= "    RewriteCond %{REQUEST_FILENAME} !-f\n";
            $htaccessContent .= "    RewriteRule ^ index.php [L]\n";
            $htaccessContent .= "</IfModule>\n\n";
            
            // Ajouter les en-têtes de sécurité
            $htaccessContent .= "# En-têtes de sécurité HTTP\n";
            $htaccessContent .= "<IfModule mod_headers.c>\n";
            $htaccessContent .= "    # Protection XSS\n";
            $htaccessContent .= "    Header set X-XSS-Protection \"1; mode=block\"\n";
            $htaccessContent .= "    \n";
            $htaccessContent .= "    # Empêcher le MIME-sniffing\n";
            $htaccessContent .= "    Header set X-Content-Type-Options \"nosniff\"\n";
            $htaccessContent .= "    \n";
            $htaccessContent .= "    # Protection contre le clickjacking\n";
            $htaccessContent .= "    Header set X-Frame-Options \"SAMEORIGIN\"\n";
            $htaccessContent .= "    \n";
            $htaccessContent .= "    # Politique de sécurité du contenu (CSP)\n";
            $htaccessContent .= "    Header set Content-Security-Policy \"default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self';\"\n";
            $htaccessContent .= "    \n";
            $htaccessContent .= "    # Strict Transport Security (HSTS)\n";
            $htaccessContent .= "    Header set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"\n";
            $htaccessContent .= "    \n";
            $htaccessContent .= "    # Référent Policy\n";
            $htaccessContent .= "    Header set Referrer-Policy \"strict-origin-when-cross-origin\"\n";
            $htaccessContent .= "    \n";
            $htaccessContent .= "    # Permissions Policy\n";
            $htaccessContent .= "    Header set Permissions-Policy \"camera=(), microphone=(), geolocation=()\"\n";
            $htaccessContent .= "</IfModule>\n";
            
            File::put($htaccessPath, $htaccessContent);
            $this->info('Fichier .htaccess créé avec les en-têtes de sécurité');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
