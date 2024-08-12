<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkingDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function didReport(): HasMany
    {
        //return subreport where working day date is the same as the report created_at date
        //but the created_at have the format YYYY-MM-DD HH:MM:SS and the date have the format YYYY-MM-DD
        //so we need to format the created_at to YYYY-MM-DD

        return $this->hasMany(Subreport::class, 'created_at', 'date')
                    ->whereRaw('DATE(created_at) = ?', [Carbon::parse($this->date)->format('Y-m-d')]);
    }
}
