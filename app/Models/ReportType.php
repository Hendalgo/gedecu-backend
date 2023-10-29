<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    use HasFactory;

    protected $table = 'reports_types';

    protected $fillable = [
        "name",
        "description",
        "type",
        "config"
    ];
    public function reports()
    {
        return $this->hasMany('App\\Models\\Report', 'type_id');
    }
}
