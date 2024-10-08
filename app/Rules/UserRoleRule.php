<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UserRoleRule implements Rule
{
    private $roles;

    public function __construct($roles)
    {
        $this->roles = array_map('intval', explode(';', $roles));
    }

    public function passes($attribute, $value)
    {
        $user = User::find($value);
        if (! $user) {
            return false;
        }
        if (in_array(intval($user->role->id), $this->roles)) {
            return true;
        }

        return false;
    }

    public function message()
    {
        return 'El usuario no es valido.';
    }
}
