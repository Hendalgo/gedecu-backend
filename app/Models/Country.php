<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public function banks(){
        return $this->hasMany('\App\Models\Bank', 'country_id');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id');
    }
}
