<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CardPayment extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'card_payments';

    protected $fillable = [
        'status', 'oid', 'name', 'transaction_amount', 'order_id', 'transaction_code','telephone', 'paid_at', 'payment_mode', 'payment_status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
