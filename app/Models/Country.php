<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "shortcode",
        "config",
        "locale",
        "img",
        "delete"
    ];
    public function banks(){
        return $this->hasMany('\App\Models\Bank', 'country_id');
    }
    public function currency(){
        return $this->hasOne('\App\Models\Currency', 'country_id');
    }
    public function stores(){
        return $this->hasMany('\App\Models\Store', 'country_id');
    }
}
