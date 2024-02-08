<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalCurrenciesHistory extends Model
{
    use HasFactory;
    protected $fillable = ['currency_id', 'total', 'created_at', 'updated_at'];
    protected $table = 'total_currencies_history';

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
