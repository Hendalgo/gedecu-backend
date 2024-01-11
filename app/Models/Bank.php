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
        'type_id'
    ];


    public function country(){
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }
    public function type(){
        return $this->belongsTo('App\Models\AccountType', 'type_id', 'id');
    }
}
