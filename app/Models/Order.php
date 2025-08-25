<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        // 'quantity',
        // 'retail_id',
        // 'product_id',
        'address',
        'status',
        'type',
        'reference',
        'customer_id',
        'payment_method',
        'dispatch_number',
        'state_id',
        'dispatch_fee',
        'original_price',
        'note'
    ];

    protected $casts = [
        'user_id'        => 'integer',
        'customer_id'    => 'integer',
        'state_id'       => 'integer',
        'status'         => 'string',
        'type'           => 'string',
        'reference'      => 'string',
        'payment_method' => 'string',
        'dispatch_number' => 'string',
        'note'           => 'string',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_product')->withPivot('quantity');
    }

    public function resells()
    {
        return $this->belongsToMany(retailProduct::class, 'order_retail_product', 'order_id', 'retail_id')->withPivot('quantity');
    }



    // public function resell()
    // {
    //     return $this->belongsTo(retailProduct::class, 'retail_id');
    // }


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
