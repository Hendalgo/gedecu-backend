<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'config',
    ];

    public function users()
    {
        return $this->hasMany('App\Models\User', 'role_id');
    }
}
