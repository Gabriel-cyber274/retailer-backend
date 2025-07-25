<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'nickname',
        'fullAddress',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'bool'
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
