<?php

namespace Store\KurdistanStore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\Customer;

class CustomerRefreshToken extends Model
{
    protected $fillable = [
        'customer_id',
        'token_hash',
        'expires_at',
        'remember',
        'user_agent',
        'ip_address',
        'last_used_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at'   => 'datetime',
        'remember'     => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
