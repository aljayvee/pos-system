<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'name',
        'address',
        'contact_number',
        'is_active',
        'country',
        'region',
        'city',
        'barangay',
        'street'
    ];

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    // Fallback accessor: If 'address' is requested but empty, construct it from parts
    public function getAddressAttribute($value)
    {
        if (!empty($value))
            return $value; // Return stored string if legacy

        $parts = array_filter([
            $this->street,
            $this->barangay,
            $this->city,
            $this->region,
            $this->country
        ]);

        return implode(', ', $parts);
    }
}