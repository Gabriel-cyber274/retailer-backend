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


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
