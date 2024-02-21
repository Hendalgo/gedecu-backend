<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'user_id',
        'currency_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function delete()
    {
        $this->delete = 1;

        return $this->save();
    }
}
