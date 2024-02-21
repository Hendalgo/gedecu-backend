<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubreportData extends Model
{
    use HasFactory;

    protected $table = 'subreport_data';

    protected $fillable = ['key', 'value', 'subreport_id'];

    public function subreport()
    {
        return $this->belongsTo(Subreport::class);
    }
}
