<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletPurchase extends Model
{
    protected $fillable = [
        'amount',
        'wallet_id',
        'user_id',
        'code',
        'is_active',
        'is_paid'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'amount' => 'decimal:2'
    ];

}
