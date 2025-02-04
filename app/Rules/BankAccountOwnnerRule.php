<?php

namespace App\Rules;

use App\Models\BankAccount;
use Illuminate\Contracts\Validation\Rule;

class BankAccountOwnnerRule implements Rule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function passes($attribute, $value)
    {
        $user = auth()->user();
        $bankAccount = BankAccount::findOrFail($value);
        if ($user->role_id === 1) {
            return true;
        }
        if ($bankAccount->user_id !== $user->id) {
            $store = $bankAccount->store->user_id;
            if ($store === $user->id) {
                return true;
            } elseif ($user->role->id !== 1) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'No tienes permiso para realizar esta accion';
    }
}
