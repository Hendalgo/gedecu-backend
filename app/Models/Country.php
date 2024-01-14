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
    public function currencies(){
        return $this->hasMany('\App\Models\Currency', 'country_id');
    }
}
