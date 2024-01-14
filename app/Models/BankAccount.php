<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = "banks_accounts";
    protected $fillable = [
        "name",
        "balance",
        "bank_id",
        "meta_data",
        "identifier",
        "user_id",
        "currency_id",
        "store_id",
        "delete"
    ];

    
    public function bank(){
        return $this->belongsTo('\App\Models\Bank', 'bank_id', 'id');
    }
    public function user(){
        return $this->belongsTo('\App\Models\User', 'user_id', 'id');
    }
    public function currency(){
        return $this->belongsTo('\App\Models\Currency', 'currency_id', 'id');
    }
}
