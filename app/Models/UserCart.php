<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'tag_id',
        'quantity',
        'status'
    ];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'tag_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function tag()
    {
        return $this->belongsTo(ProductTags::class, 'tag_id');
    }
}
