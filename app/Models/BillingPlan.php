<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'features',
        'max_tenants',
        'active',
        'is_featured',
        'sort_order'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'billing_cycle' => 'integer',
        'features' => 'array',
        'max_tenants' => 'integer',
        'active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Obtenir les tenants associés à ce plan de facturation.
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'billing_plan_id');
    }

    /**
     * Obtenir le prix formaté avec la devise.
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . strtoupper($this->currency);
    }

    /**
     * Obtenir la période de facturation formatée.
     */
    public function getBillingCycleTextAttribute()
    {
        return $this->billing_cycle === 1 ? 'Mensuel' : $this->billing_cycle . ' mois';
    }

    /**
     * Scope pour les plans actifs.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope pour les plans en vedette.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
