<?php

namespace Store\KurdistanStore\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'gateway',
        'reference',
        'amount',
        'currency',
        'status',
        'payload',
        'response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'payload' => 'array',
            'response' => 'array',
            'paid_at' => 'datetime',
        ];
    }
}
