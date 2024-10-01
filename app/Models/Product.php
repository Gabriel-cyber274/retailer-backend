<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'product_image',
        'description',
        'price',
        'product_code',
        'suggested_profit',
        'default_tag'
    ];


    public function tags()
    {
        return $this->hasMany(ProductTags::class, 'product_id');
    }
}
