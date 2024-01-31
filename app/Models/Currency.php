<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "shortcode",
        "symbol",
        "country_id",
    ];
    public function country(){
        return $this->belongsTo('\App\Models\Country', 'country_id');
    }
    public function delete()
    {
        $this->delete = 1;
        return $this->save();
    }
}
