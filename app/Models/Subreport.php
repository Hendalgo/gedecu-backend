<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subreport extends Model
{
    use HasFactory;

    protected $fillable = [
        "amount",
        "duplicate",
        "report_id",
        "currency_id",
        "data",
    ];

    public function report(){
        return $this->belongsTo('\App\Models\Report', 'report_id');
    }
    public function currency(){
        return $this->belongsTo('\App\Models\Currency', 'currency_id');
    }
}
