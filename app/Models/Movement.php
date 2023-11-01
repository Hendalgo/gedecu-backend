<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'type',
        'bank_account_id',
        'bank_account_amount', 
        'report_id'
    ];
    public function report(){
        return $this->belongsTo('\App\Models\Report', 'report_id', 'id');
    }
}
