<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminProfitTransfer extends Model
{
    protected $fillable = [
        'amount_piconero',
        'tx_hash',
        'wallet_address',
        'status',
        'error_message',
    ];
}
