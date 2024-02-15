<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inconsistence extends Model
{
    use HasFactory;

    protected $fillable = [
        'subreport_id',
        'verified',
        'data'
    ];

    public function subreport()
    {
        return $this->belongsTo(Subreport::class);
    }
}
