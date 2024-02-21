<?php

namespace App\Rules;

use App\Models\Store;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserHasStoreRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $store = Store::where('user_id', auth()->id())->first();
        if (! $store) {
            $fail('Debe poseer un local asignado para crear este reporte.');
        }
    }
}
