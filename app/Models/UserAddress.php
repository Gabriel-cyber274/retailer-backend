<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id',
        'nickname',
        'fullAddress',
        'state_id',
        'is_default',
    ];

    protected $casts = [
        'state_id' => 'integer',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'bool'
        ];
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
