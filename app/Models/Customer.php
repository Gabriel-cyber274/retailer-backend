<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'name',
        'email',
        'phone_no',
        'note'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'customer_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
