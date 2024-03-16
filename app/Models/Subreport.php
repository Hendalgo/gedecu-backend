<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subreport extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'duplicate',
        'report_id',
        'currency_id',
        'duplicate_status',
        'created_at',
    ];

    public function report()
    {
        return $this->belongsTo('\App\Models\Report', 'report_id');
    }

    public function currency()
    {
        return $this->belongsTo('\App\Models\Currency', 'currency_id');
    }

    public function inconsistence()
    {
        return $this->hasOne('\App\Models\Inconsistence', 'subreport_id');
    }

    public function data()
    {
        return $this->hasMany('\App\Models\SubreportData', 'subreport_id');
    }
}
