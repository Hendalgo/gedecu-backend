<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function type(){
        return $this->belongsTo('App\Models\ReportType', 'type_id', 'id');
    }
    public function country(){
        return $this->belongsTo('App\Models\Country', 'country_id', 'id');
    }
    public function bank(){
        return $this->belongsTo('App\Models\Bank', 'bank_id', 'id');
    }
}
