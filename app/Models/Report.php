<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_data',
        'user_id',
        'type_id',
        'delete'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function type(){
        return $this->belongsTo('App\Models\ReportType', 'type_id', 'id');
    }
    public function subreports(){
        return $this->hasMany('App\Models\Subreport', 'report_id', 'id');
    }
}
