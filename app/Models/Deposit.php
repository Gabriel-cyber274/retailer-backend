<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'customer_id',
        'status',
        'order_id'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }



    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
