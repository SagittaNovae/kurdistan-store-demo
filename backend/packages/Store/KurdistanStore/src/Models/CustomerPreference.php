<?php

namespace Store\KurdistanStore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\Customer;

class CustomerPreference extends Model
{
    protected $fillable = [
        'customer_id',
        'notify_order_updates',
        'notify_promotions',
        'preferred_language',
    ];

    protected $casts = [
        'notify_order_updates' => 'boolean',
        'notify_promotions'    => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
