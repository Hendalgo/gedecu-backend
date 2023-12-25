<?php

namespace App\Providers;

use App\Rules\BankAccountOwnnerRule;
use App\Rules\UserRoleRule;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setUTF8(true);
        Carbon::setLocale(config('app.locale'));
        setlocale(LC_ALL, 'es_MX', 'es', 'ES', 'es_MX.utf8');
        Validator::extend('user_role', function ($attribute, $value, $parameters, $validator) {
            $rule = new UserRoleRule($parameters[0]);
            return $rule->passes($attribute, $value);
        });
        Validator::extend('bank_account_ownner', function ($attribute, $value, $parameters, $validator) {
            $rule = new BankAccountOwnnerRule($parameters[0]);
            return $rule->validate($attribute, $value, function ($message) use ($attribute) {
                throw new \Illuminate\Validation\ValidationException([$attribute => $message]);
            });
        });
    }
}
