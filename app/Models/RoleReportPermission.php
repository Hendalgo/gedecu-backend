<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleReportPermission extends Model
{
    use HasFactory;
    protected $table = 'roles_reports_permissions';
    protected $fillable = [
        'role_id',
        'report_type_id',
    ];

    public function role(){
        return $this->belongsTo(Role::class);
    }
    public function report_type(){
        return $this->belongsTo(ReportType::class);
    }
}
