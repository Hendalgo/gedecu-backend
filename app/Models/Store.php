<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'location',
        'user_id',
        'country_id'
    ];

    public function country(){
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function account(){
        return $this->hasOne('App\Models\BankAccount', 'store_id', 'id');
    }

    protected static function booted()
    {
        static::created(function ($store) {
            $account = new BankAccount();
            $account->name = "Efectivo";
            $account->identifier = "Efectivo";
            $account->store_id = $store->id;
            $account->currency_id = Currency::where('country_id', $store->country_id)->first()->id;
            $account->balance = fake()->numberBetween(1000, 1000000);
            $account->save();
        });
    }
}
