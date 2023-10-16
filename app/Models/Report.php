<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'payment_reference',
        'inconsistence_check',
        'duplicated',
        'duplicated_status',
        'notes',
        'bank_amount',
        'meta_data',
        'user_id',
        'store_id',
        'type_id',
        'bank_id'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    public function type(){
        return $this->belongsTo('App\Models\ReportType', 'type_id', 'id');
    }
    public function bank(){
        return $this->belongsTo('App\Models\Bank', 'bank_id', 'id');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }
}