<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class retailProduct extends Model
{
    use HasFactory;

    protected $fillable  = [
        'gain',
        'product_id',
        'user_id',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }




    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
