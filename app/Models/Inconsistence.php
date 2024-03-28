<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inconsistence extends Model
{
    use HasFactory;

    protected $fillable = [
        'subreport_id',
        'associated_id',
        'verified',
        'data',
    ];

    public function subreport()
    {
        return $this->belongsToMany(Subreport::class,
            'inconsistences', // Intermediate table
            'associated_id', // Foreign key current model
            'subreport_id' // Foreign key related model
        );
    }
}
