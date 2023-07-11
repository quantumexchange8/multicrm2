<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements JWTSubject, HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, InteractsWithMedia;

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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getMonthlyDeposit()
    {
        return Payment::query()
            ->where('user_id', Auth::id())
            ->where('category', '=', 'payment')
            ->where('type', '=', 'Deposit')
            ->where('status', '=', 'Successful')
            ->whereBetween('created_at', [
                now()->startOfMonth(), // Start of the current month
                now()->endOfMonth(),   // End of the current month
            ])
            ->sum('amount');
    }

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
