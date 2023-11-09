<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'img',
        'meta_data',
        'country_id',
        'currency_id'
    ];


    public function country(){
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id');
    }
}
