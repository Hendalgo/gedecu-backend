<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;
    protected $table = "accounts_types";

    protected $fillable = [
        "name",
        "description",
    ];

    public function bank_accounts(){
        return $this->hasMany('\App\Models\BankAccount', 'account_type_id', 'id');
    }
}
