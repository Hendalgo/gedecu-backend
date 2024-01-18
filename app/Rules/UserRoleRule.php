<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UserRoleRule implements Rule
{ 
    private $role;

    public function __construct($role)
    {
        $this->role = $role;
    }
    public function passes($attribute, $value)
    {   
        $user = User::find($value);
        if (!$user) return false;
        if ($user->role->id == $this->role) {
            return true;
        }
        return false;
    }
    public function message()
    {
        return 'El usuario no es valido.';
    }
}