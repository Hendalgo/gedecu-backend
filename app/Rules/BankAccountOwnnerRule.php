<?php

namespace App\Rules;

use App\Models\Bank;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BankAccountOwnnerRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = auth()->user();
        $bankAccount = Bank::find($value);
        if ($bankAccount->user_id !== $user->id) {
            if ($user->role->id !== 1) {
                $fail('No tienes permiso para realizar esta accion');
            }
        }
    }
}
