<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payments';

    protected $fillable = [
        'oid', 'sid', 'session_id', 'firstname', 'lastname', 'account', 'transaction_amount', 'order_id', 'transation_code','telephone', 'paid_at', 'payment_mode', 'payment_status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
