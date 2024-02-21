<?php

namespace App\Http\Controllers;

use App\Models\AccountType;

class AccountTypeController extends Controller
{
    public function index()
    {
        return response()->json(AccountType::all(), 200);
    }
}
