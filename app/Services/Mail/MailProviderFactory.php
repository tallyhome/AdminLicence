<?php

namespace App\Services\Mail;

use App\Contracts\MailProviderInterface;
use InvalidArgumentException;

class MailProviderFactory
{
    /**
     * Liste des fournisseurs d'email disponibles
     *
     * @var array
     */
    protected static array $providers = [
        'smtp' => SmtpMailProvider::class,
        'mailgun' => MailgunProvider::class,
        'phpmail' => PhpMailProvider::class,
        'mailchimp' => MailchimpProvider::class,
    ];

    /**
     * Créer une instance du fournisseur d'email spécifié
     *
     * @param string $provider
     * @param array $config
     * @return MailProviderInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $provider, array $config = []): MailProviderInterface
    {
        if (!isset(self::$providers[$provider])) {
            throw new InvalidArgumentException("Fournisseur d'email non supporté : {$provider}");
        }

        $providerClass = self::$providers[$provider];
        $instance = new $providerClass();
        
        if (!empty($config)) {
            $instance->configure($config);
        }

        return $instance;
    }

    /**
     * Obtenir la liste des fournisseurs d'email disponibles
     *
     * @return array
     */
    public static function getAvailableProviders(): array
    {
        return array_keys(self::$providers);
    }

    /**
     * Ajouter un nouveau fournisseur d'email
     *
     * @param string $name
     * @param string $class
     * @return void
     */
    public static function addProvider(string $name, string $class): void
    {
        if (!class_exists($class) || !is_subclass_of($class, MailProviderInterface::class)) {
            throw new InvalidArgumentException("La classe du fournisseur doit implémenter MailProviderInterface");
        }

        self::$providers[$name] = $class;
    }
}