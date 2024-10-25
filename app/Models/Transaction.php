<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'subreport_id',
        'account_id',
        'balance_id',
        'currency_id',
        'amount',
        'created_at',
    ];

    public function subreport()
    {
        return $this->belongsTo(Subreport::class);
    }
    public function account()
    {
        return $this->belongsTo(BankAccount::class);
    }
    public function balance()
    {
        return $this->belongsTo(UserBalance::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
