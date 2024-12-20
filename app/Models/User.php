<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

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
        'role_id',
        'permissions',
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

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }

    public function lastReport()
    {
        return $this->hasOne('App\Models\Report', 'user_id', 'id')->latest();
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }

    public function balance()
    {
        return $this->hasOne('\App\Models\UserBalance', 'user_id');
    }

    public function store()
    {
        return $this->hasOne('\App\Models\Store', 'user_id');
    }

    public function accounts()
    {
        return $this->hasMany('\App\Models\BankAccount', 'user_id');
    }

    public function workingDays()
    {
        //In this relationship just get the working days of the current week
        $workingDays = $this->hasMany('App\Models\WorkingDay', 'user_id');

        //return $workingDays; and if what days did a report
        return $workingDays->with('didReport');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function delete()
    {
        foreach ($this->accounts as $account) {
            $account->delete();
        }
        //$this->balance->delete();
        $this->store()->update(['user_id' => null]);

        $this->delete = 1;

        return $this->save();
    }

    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->role_id === 5 || $user->role_id === 6) {
               foreach(json_decode($user->permissions)->allowed_currencies as $currency){
                    $user->balance()->create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'currency_id' => $currency,
                    ]);
               }
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
