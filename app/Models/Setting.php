<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'description'
    ];

    /**
     * Récupère un paramètre par sa clé
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getByKey(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        return json_decode($setting->value, true) ?? $setting->value;
    }
    
    /**
     * Alias de getByKey pour une syntaxe plus simple
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return self::getByKey($key, $default);
    }

    /**
     * Définit ou met à jour un paramètre
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return Setting
     */
    public static function setByKey(string $key, $value, ?string $description = null)
    {
        $setting = self::firstOrNew(['key' => $key]);
        
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }
        
        $setting->value = $value;
        
        if ($description) {
            $setting->description = $description;
        }
        
        $setting->save();
        
        return $setting;
    }
    
    /**
     * Alias de setByKey pour une syntaxe plus simple
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @return Setting
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        return self::setByKey($key, $value, $description);
    }
}
