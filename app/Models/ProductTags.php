<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTags extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag_image',
        'description',
        'product_id',
        'tag_code',
    ];

    protected $casts = [
        'product_id' => 'integer',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cart()
    {
        return $this->hasMany(UserCart::class, 'tag_id');
    }
}
