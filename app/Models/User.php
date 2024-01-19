<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'country_id',
        'role_id'
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function country(){
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }
    public function role(){
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }
    public function balance(){
        return $this->hasOne('\App\Models\UserBalance', 'user_id');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->role_id === 5 || $user->role_id === 6){
                $user->balance()->create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'currency_id' => Currency::where('country_id', $user->country_id)->first()->id,
                ]);
            }
        });
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
}
