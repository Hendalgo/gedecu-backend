<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportTypeValidations extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'validation', 'report_type_id', 'validation_role'];
    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }
}
