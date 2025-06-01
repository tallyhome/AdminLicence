<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EncryptionService
{
    /**
     * Chiffre une valeur sensible
     *
     * @param string $value
     * @return string
     */
    public function encrypt(string $value): string
    {
        try {
            // Vérifier si le chiffrement est activé dans la configuration
            if (env('SECURITY_ENCRYPT_LICENCE_KEYS', true)) {
                return Crypt::encryptString($value);
            }
            
            // Si le chiffrement est désactivé, retourner la valeur telle quelle
            return $value;
        } catch (\Exception $e) {
            Log::error('Erreur lors du chiffrement d\'une valeur: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            // En cas d'erreur, retourner la valeur non chiffrée
            return $value;
        }
    }
    
    /**
     * Déchiffre une valeur sensible
     *
     * @param string $encryptedValue
     * @return string
     */
    public function decrypt(string $encryptedValue): string
    {
        try {
            // Vérifier si le chiffrement est activé dans la configuration
            if (env('SECURITY_ENCRYPT_LICENCE_KEYS', true)) {
                // Vérifier si la valeur est déjà chiffrée
                if ($this->isEncrypted($encryptedValue)) {
                    return Crypt::decryptString($encryptedValue);
                }
            }
            
            // Si le chiffrement est désactivé ou si la valeur n'est pas chiffrée, retourner la valeur telle quelle
            return $encryptedValue;
        } catch (\Exception $e) {
            Log::error('Erreur lors du déchiffrement d\'une valeur: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            // En cas d'erreur, retourner la valeur telle quelle
            return $encryptedValue;
        }
    }
    
    /**
     * Vérifie si une valeur est déjà chiffrée
     *
     * @param string $value
     * @return bool
     */
    public function isEncrypted(string $value): bool
    {
        // Vérifier si la valeur a le format d'une chaîne chiffrée par Laravel
        return strpos($value, 'eyJpdiI6') === 0;
    }
    
    /**
     * Rechiffre toutes les clés de licence dans la base de données
     *
     * @return array
     */
    public function reencryptAllLicenceKeys(): array
    {
        try {
            $serialKeys = \App\Models\SerialKey::all();
            $count = 0;
            
            foreach ($serialKeys as $key) {
                // Déchiffrer la clé si elle est déjà chiffrée
                $decryptedKey = $this->decrypt($key->serial_key);
                
                // Chiffrer la clé avec la nouvelle méthode
                $encryptedKey = $this->encrypt($decryptedKey);
                
                // Mettre à jour la clé dans la base de données
                $key->serial_key = $encryptedKey;
                $key->save();
                
                $count++;
            }
            
            return [
                'success' => true,
                'message' => "Rechiffrement réussi de {$count} clés de licence",
                'count' => $count
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du rechiffrement des clés de licence: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors du rechiffrement des clés de licence: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }
}
