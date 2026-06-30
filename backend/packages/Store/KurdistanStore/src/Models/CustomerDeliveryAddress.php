<?php

namespace Store\KurdistanStore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\Customer;

class CustomerDeliveryAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'label',
        'nickname',
        'address_text',
        'governorate',
        'city',
        'address_line',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
