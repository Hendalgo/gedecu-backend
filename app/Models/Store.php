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
}
