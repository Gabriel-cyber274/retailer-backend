<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'address',
        'acc_balance',
        'city',
        'state',
        'verification_code',
        'admin',
        'shop_name',
        'shop_id',
        'phone_number',
        'verification_expires_at',
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'admin' => 'bool'
        ];
    }

    public function isAdmin(): bool
    {
        return $this->admin === true;
    }


    public function retails()
    {
        return $this->hasMany(retailProduct::class, 'user_id');
    }


    public function customers()
    {
        return $this->hasMany(Customer::class, 'user_id');
    }


    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class, 'user_id');
    }


    public function deposits()
    {
        return $this->hasMany(Deposit::class, 'user_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function savedProduct()
    {
        return $this->hasMany(SavedProduct::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'user_id');
    }

    public function cart()
    {
        return $this->hasMany(UserCart::class, 'user_id');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }
}
