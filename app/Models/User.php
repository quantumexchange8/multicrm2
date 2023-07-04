<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $appends = ['full_name'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'investor_password',
        'phone_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tradingUsers()
    {
        return $this->hasMany(TradingUser::class, 'user_id', 'id');
    }
    public function children()
    {
        return $this->hasMany(User::class, 'referral', 'ib_id');
    }
    public function parent()
    {
        return $this->belongsTo(User::class, 'referral', 'ib_id');
    }
    public function downline()
    {
        return $this->hasMany(User::class, 'upline_id', 'id');
    }
    public function upline()
    {
        return $this->belongsTo(User::class, 'upline_id', 'id');
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name;
    }

    public function getAddressAttribute()
    {
        return $this->address_1 . ', ' . ($this->address_2 ? $this->address_2 . ', ' : '') . $this->postcode . ', ' . $this->country;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id', 'id');
    }

    ////https://stackoverflow.com/a/62297282
    public function scopewhereFullName($query, $name)
    {
        return $query->where(DB::raw("REPLACE(CONCAT(COALESCE(first_name,''),' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')),'  ',' ')"), 'like', '%' . $name . '%');
    }
}
