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
        'brand_name',
        'video_url',
        'default_tag',
        'in_stock',
        'quantity',
        'cost_price',
    ];

    protected function casts(): array
    {
        return [
            'in_stock' => 'bool'
        ];
    }



    public function tags()
    {
        return $this->hasMany(ProductTags::class, 'product_id');
    }


    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }


    public function retails()
    {
        return $this->hasMany(retailProduct::class, 'product_id');
    }


    public function featuredimages()
    {
        return $this->hasMany(ProductFeatureImages::class, 'product_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id');
    }
}
