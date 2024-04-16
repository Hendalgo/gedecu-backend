<?php

namespace Database\Seeders;

use App\Models\ReportTypeValidations;
use Illuminate\Database\Seeder;

class ReportTypeValidationsSeeder extends Seeder
{
    public function run()
    {
        ReportTypeValidations::query()->delete();

        ReportTypeValidations::create([
            'name' => 'supplier_id',
            'validation' => 'required|exists:users,id|user_role:4',
            'validation_role' => 'all',
            'report_type_id' => 1,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 1,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 1,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 1,
        ]);
        ReportTypeValidations::create([
            'name' => 'reference',
            'validation' => 'required',
            'validation_role' => 'all',
            'report_type_id' => 1,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 1,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id|user_role:2',
            'validation_role' => 'all',
            'report_type_id' => 2,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 2,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 2,
        ]);
        ReportTypeValidations::create([
            'name' => 'reference',
            'validation' => 'required',
            'validation_role' => 'all',
            'report_type_id' => 2,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 2,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 3,
        ]);
        ReportTypeValidations::create([
            'name' => 'transferences_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 3,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 3,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 3,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 3,
        ]);
        ReportTypeValidations::create([
            'name'=> 'bank_id',
            'validation' => 'required|exists:banks,id',
            'validation_role' => 'all',
            'report_type_id' => 4,
        ]);
        ReportTypeValidations::create([
            'name' => 'store_id',
            'validation' => 'required|exists:stores,id',
            'validation_role' => 'all',
            'report_type_id' => 4,
        ]);
        ReportTypeValidations::create([
            'name' => 'transferences_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 4,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 4,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 4,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 4,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 4,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 5,
        ]);
        ReportTypeValidations::create([
            'name' => 'transferences_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 5,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 5,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 5,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 5,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id|user_role:2',
            'validation_role' => 'all',
            'report_type_id' => 6,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 6,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 6,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 6,
        ]);
        ReportTypeValidations::create([
            'name' => 'senderAccount_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 7,
        ]);
        ReportTypeValidations::create([
            'name' => 'receiverAccount_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 7,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 7,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 7,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 8,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 8,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 8,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 9,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 9,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 9,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 10,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 10,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 10,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 11,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 11,
        ]);
        ReportTypeValidations::create([
            'name' => 'motive',
            'validation' => 'required',
            'validation_role' => 'all',
            'report_type_id' => 11,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 11,
        ]);
        ReportTypeValidations::create([
            'name' => 'transferences_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 12,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 12,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 12,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 12,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 13,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 13,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 14,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 14,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 14,
        ]);
        ReportTypeValidations::create([
            'name' => 'store_id',
            'validation' => 'required|exists:stores,id',
            'validation_role' => 'all',
            'report_type_id' => 15,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 15,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 15,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 15,
        ]);
        ReportTypeValidations::create([
            'name' => 'senderAccount_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 16,
        ]);
        ReportTypeValidations::create([
            'name' => 'receiverAccount_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 16,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 16,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 16,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id',
            'validation_role' => 'all',
            'report_type_id' => 17,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 17,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 18,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 18,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 18,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 19,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 19,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 19,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 20,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 20,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 20,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 21,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 21,
        ]);
        ReportTypeValidations::create([
            'name' => 'motive',
            'validation' => 'required',
            'validation_role' => 'all',
            'report_type_id' => 21,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 21,
        ]);
        ReportTypeValidations::create([
            'name' => 'transferences_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 22,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 22,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 22,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 22,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 22,
        ]);
        ReportTypeValidations::create([
            'name' => 'bank_id',
            'validation' => 'required|exists:banks,id',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'store_id',
            'validation' => 'required|exists:stores,id',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'transferences_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id|user_role:2',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 23,
        ]);
        ReportTypeValidations::create([
            'name' => 'transferences_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 24,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 24,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 24,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 24,
        ]);
        ReportTypeValidations::create([
            'name' => 'store_id',
            'validation' => 'required|exists:stores,id',
            'validation_role' => 'all',
            'report_type_id' => 25,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id',
            'validation_role' => 'all',
            'report_type_id' => 25,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 25,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 25,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id|user_role:2',
            'validation_role' => 'all',
            'report_type_id' => 26,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id',
            'validation_role' => 'all',
            'report_type_id' => 26,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 26,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 26,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 27,
        ]);
        ReportTypeValidations::create([
            'name' => 'store_id',
            'validation' => 'required|exists:stores,id',
            'validation_role' => 'all',
            'report_type_id' => 27,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 27,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 28,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id',
            'validation_role' => 'all',
            'report_type_id' => 28,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 28,
        ]);
        ReportTypeValidations::create([
            'name' => 'deposits_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 28,
        ]);
        ReportTypeValidations::create([
            'name' => 'supplier_id',
            'validation' => 'required|exists:users,id|user_role:4',
            'validation_role' => 'all',
            'report_type_id' => 29,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 29,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 29,
        ]);
        ReportTypeValidations::create([
            'name' => 'deposits_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 29,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 29,
        ]);
        /*Entrega a caja*/
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id|user_role:6',
            'validation_role' => 'all',
            'report_type_id' => 30,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 30,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 30,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 30,
        ]);
        /*Entrega efectivo (A jefe de depositante)*/
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id|user_role:7',
            'validation_role' => 'all',
            'report_type_id' => 45,
        ]);
        ReportTypeValidations::create([
            'name' => 'motive',
            'validation' => 'required',
            'validation_role' => 'all',
            'report_type_id' => 45,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 45,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 45,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 45,
        ]);
        
        /*Gastos*/
        ReportTypeValidations::create([
            'name' => 'motive',
            'validation' => 'required',
            'validation_role' => 'all',
            'report_type_id' => 46,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 46,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 46,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 46,
        ]);


        /** */
        ReportTypeValidations::create([
            'name' => 'store_id',
            'validation' => 'required|exists:stores,id',
            'validation_role' => 'all',
            'report_type_id' => 31,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 31,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 31,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 31,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 32,
        ]);
        ReportTypeValidations::create([
            'name' => 'store_id',
            'validation' => 'required|exists:stores,id',
            'validation_role' => 'all',
            'report_type_id' => 32,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 32,
        ]);
        ReportTypeValidations::create([
            'name' => 'deposits_quantity',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 32,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 32,
        ]);
        ReportTypeValidations::create([
            'name' => 'supplier_id',
            'validation' => 'required|exists:users,id|user_role:4',
            'validation_role' => 'all',
            'report_type_id' => 33,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 33,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 33,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 33,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 34,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id',
            'validation_role' => 'all',
            'report_type_id' => 34,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 34,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 35,
        ]);
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id',
            'validation_role' => 'all',
            'report_type_id' => 35,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 35,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 36,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 36,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 36,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean',
            'validation_role' => 'all',
            'report_type_id' => 36,
        ]);

        /*Nomina*/

        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 37,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 37,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 37,
        ]);

        /*Comisiones por giros */

        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 38,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 38,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 38,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 38,
        ]);

        /*Billetera Jefe*/
        ReportTypeValidations::create([
            'name' => 'user_id',
            'validation' => 'required|exists:users,id|user_role:7',
            'validation_role' => 'all',
            'report_type_id' => 39,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 39,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 39,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 39,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 39,
        ]);

        /*Billetera Cliente (Transferencias)*/
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 40,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 40,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 40,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 40,
        ]);
        ReportTypeValidations::create([
            'name' => 'conversionCurrency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 40,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 40,
        ]);
        ReportTypeValidations::create([
            'name' => 'wallet_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 41,
        ]);

        /*Billetera Cliente (Efectivo)*/

        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 41,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 41,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 41,
        ]);
        ReportTypeValidations::create([
            'name' => 'conversionCurrency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 40,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 41,
        ]);
        ReportTypeValidations::create([
            'name' => 'wallet_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 41,
        ]);

        /*Billetera Cliente Egreso (Transferencias)*/
        ReportTypeValidations::create([
            'name' => 'wallet_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 42,
        ]);
        ReportTypeValidations::create([
            'name' => 'account_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 42,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 42,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 42,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 42,
        ]);
        ReportTypeValidations::create([
            'name' => 'conversionCurrency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 42,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 42,
        ]);

        /*Billetera Cliente Egreso (Efectivo)*/
        ReportTypeValidations::create([
            'name' => 'wallet_id',
            'validation' => 'required|exists:banks_accounts,id|bank_account_owner',
            'validation_role' => 'all',
            'report_type_id' => 43,
        ]);
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 43,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 43,
        ]);
        ReportTypeValidations::create([
            'name' => 'rate',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 43,
        ]);
        ReportTypeValidations::create([
            'name' => 'conversionCurrency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 43,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 43,
        ]);

        /*Otros (Efectivo)*/
        ReportTypeValidations::create([
            'name' => 'amount',
            'validation' => 'required|numeric',
            'validation_role' => 'all',
            'report_type_id' => 44,
        ]);
        ReportTypeValidations::create([
            'name' => 'currency_id',
            'validation' => 'required|exists:currencies,id',
            'validation_role' => 'all',
            'report_type_id' => 44,
        ]);
        ReportTypeValidations::create([
            'name' => 'motive',
            'validation' => 'required',
            'validation_role' => 'all',
            'report_type_id' => 44,
        ]);
        ReportTypeValidations::create([
            'name' => 'isDuplicated',
            'validation' => 'required|boolean|is_false',
            'validation_role' => 'all',
            'report_type_id' => 44,
        ]);
    }
}
