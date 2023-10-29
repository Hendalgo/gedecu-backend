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
        "account_number",
        "balance",
        "bank_id",
        "meta_data"
    ];

    
    public function report(){
        return $this->belongsTo('\App\Models\Bank', 'bank_id', 'id');
    }
}
