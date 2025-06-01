<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Log\Events\MessageLogged;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configurer la journalisation sécurisée uniquement en production
        if (app()->environment('production')) {
            // Écouter les événements de journalisation
            Log::listen(function (MessageLogged $event) {
                // Filtrer les informations sensibles des messages de log
                $this->filterSensitiveData($event);
            });
        }
    }

    /**
     * Filtre les informations sensibles des messages de log
     *
     * @param MessageLogged $event
     * @return void
     */
    private function filterSensitiveData(MessageLogged $event): void
    {
        // Liste des mots-clés sensibles à filtrer
        $sensitiveKeywords = [
            'password', 'mot de passe', 'token', 'secret', 'api_key', 'api_secret', 
            'serial_key', 'clé de série', 'licence_key', 'authorization', 'bearer'
        ];

        // Vérifier si le contexte contient des informations sensibles
        if (isset($event->context) && is_array($event->context)) {
            foreach ($sensitiveKeywords as $keyword) {
                // Filtrer les clés sensibles dans le contexte
                foreach ($event->context as $key => $value) {
                    if (is_string($key) && stripos($key, $keyword) !== false) {
                        // Remplacer la valeur par [REDACTED]
                        $event->context[$key] = '[REDACTED]';
                    }
                }
            }
        }

        // Vérifier si le message contient des informations sensibles
        if (isset($event->message) && is_string($event->message)) {
            // Rechercher les patterns comme "password=123456" ou "api_key: abc123"
            foreach ($sensitiveKeywords as $keyword) {
                $patterns = [
                    '/' . preg_quote($keyword, '/') . '\s*[=:]\s*[^\s,;]+/i',
                    '/' . preg_quote($keyword, '/') . '\s*[=:]\s*[\'"][^\'"]++[\'"]/i'
                ];

                foreach ($patterns as $pattern) {
                    $event->message = preg_replace($pattern, $keyword . '=[REDACTED]', $event->message);
                }
            }
        }
    }
}
