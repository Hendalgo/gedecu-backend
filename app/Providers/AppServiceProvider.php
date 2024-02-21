<?php

namespace App\Providers;

use App\Rules\BankAccountOwnnerRule;
use App\Rules\UserHasStoreRule;
use App\Rules\UserRoleRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        Validator::extend('bank_account_owner', function ($attribute, $value, $parameters, $validator) {
            $rule = new BankAccountOwnnerRule($parameters);

            return $rule->passes($attribute, $value);
        });
        Validator::extend('user_has_store', function ($attribute, $value, $parameters, $validator) {
            $rule = new UserHasStoreRule();

            return $rule->validate($attribute, $value, function ($message) {
                return $message;
            });
        });
        Validator::extend('is_false', function ($attribute, $value, $parameters, $validator) {
            return ! $value;
        });
        Validator::replacer('is_false', function ($message, $attribute, $rule, $parameters) {
            return "El campo $attribute debe ser falso.";
        });
    }
}
