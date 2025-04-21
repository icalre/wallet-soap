<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletCharge extends Model
{
    protected $fillable = [
        'amount',
        'wallet_id',
        'user_id'
    ];

}
