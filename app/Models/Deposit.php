<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'retail_id',
        'quantity',
        'amount',
        'customer_id',
        'status'
    ];


    public function resell()
    {
        return $this->belongsTo(retailProduct::class, 'retail_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }


    
    public function orders()
    {
        return $this->hasOne(Order::class, 'deposit_id');
    }



}
