<?php

namespace Store\KurdistanStore\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = [
        'governorate',
        'district',
        'flat_rate',
        'estimated_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'flat_rate' => 'decimal:2',
            'estimated_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
