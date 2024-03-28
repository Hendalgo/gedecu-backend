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

    public function inconsistences()
    {
        return $this->belongsToMany('\App\Models\Subreport', 
            'inconsistences', // Intermediate table
            'associated_id', // Foreign key current model
            'subreport_id' // Foreign key related model
        );
    }
    public function data()
    {
        return $this->hasMany('\App\Models\SubreportData', 'subreport_id');
    }
}
