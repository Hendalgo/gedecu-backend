<?php

use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DuplicatedReportController;
use App\Http\Controllers\InconsistenceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkingDayController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth',

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh-token', [AuthController::class, 'refreshToken']);
});
Route::middleware('auth.veryfied')->group(function () {

    Route::middleware('check.permissions')->group(function () {
    
        Route::middleware('assigned.workingdays')->group(function () {
            /**Banks Accounts */
            Route::post('bank-accounts', [BankAccountController::class, 'store']);
            Route::put('bank-accounts/{id}', [BankAccountController::class, 'update']);
            Route::delete('bank-accounts/{id}', [BankAccountController::class, 'destroy']);

            /**Reports */

            Route::post('reports', [ReportController::class, 'store']);
            Route::put('reports/{id}', [ReportController::class, 'update']);
            Route::delete('reports/subreports/{id}', [ReportController::class, 'destroy']);
        });

        Route::get('user', [AuthController::class, 'me']);
        /*
            Bank types
        */
        Route::get('banks/types', [AccountTypeController::class, 'index']);

        /*
        * Banks Routes
        */
        Route::post('banks', [BankController::class, 'store']);
        Route::get('banks', [BankController::class, 'index']);
        Route::get('banks/{id}', [BankController::class, 'show']);
        Route::put('banks/{id}', [BankController::class, 'update']);
        Route::delete('banks/{id}', [BankController::class, 'destroy']);

        /**
         * Banks Accounts Routes
         */
        Route::get('bank-accounts', [BankAccountController::class, 'index']);
        /*
            Users routes
        */
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/balances', [UserController::class, 'getBalances']);
        Route::get('users/roles', [UserController::class, 'getUserRoles']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{id}', [UserController::class, 'update']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
        /*
        * Countries Routes
        */
        Route::get('countries', [CountryController::class, 'index']);
        Route::get('countries/banks', [CountryController::class, 'getBanksCount']);
        Route::get('countries/{id}', [CountryController::class, 'show']);
        Route::post('countries', [CountryController::class, 'store']);
        Route::put('countries/{id}', [CountryController::class, 'update']);
        Route::delete('countries/{id}', [CountryController::class, 'destroy']);
        /*
        * Currencies Routes
        */
        Route::get('currencies', [CurrencyController::class, 'index']);
        Route::get('currencies/{id}', [CurrencyController::class, 'show']);
        Route::post('currencies', [CurrencyController::class, 'store']);
        Route::put('currencies/{id}', [CurrencyController::class, 'update']);
        Route::delete('currencies/{id}', [CurrencyController::class, 'destroy']);

        /*
        * Reports Types Routes
        * */
        Route::get('reports/types', [ReportTypeController::class, 'index']);
        /**
         * Duplicate Reports Routes
         */
        Route::get('reports/duplicated', [DuplicatedReportController::class, 'index']);
        Route::put('reports/duplicated/{id}', [DuplicatedReportController::class, 'duplicated_complete']);
        Route::get('reports/duplicated/{id}', [DuplicatedReportController::class, 'show']);
        /*
        * Reports Routes
        */
        Route::get('reports', [ReportController::class, 'index']);
        Route::get('reports/inconsistences', [ReportController::class, 'getInconsistences']);
        Route::get('reports/{id}', [ReportController::class, 'show']);
        /*
        * Stores Routes
        */
        Route::get('stores', [StoreController::class, 'index']);
        Route::get('stores/{id}', [StoreController::class, 'show']);
        Route::post('stores', [StoreController::class, 'store']);
        Route::put('stores/{id}', [StoreController::class, 'update']);
        Route::delete('stores/{id}', [StoreController::class, 'destroy']);
        /*
        * Role Routes
        */
        Route::get('role', [RoleController::class, 'index']);

        /*Statistics*/
        Route::get('statistics', [StatisticsController::class, 'getMovementsByPeriods']);
        Route::get('statistics/total-currencies', [StatisticsController::class, 'getTotalByCurrency']);
        Route::get('statistics/total-banks', [StatisticsController::class, 'getTotalByBank']);
        Route::get('statistics/total-banks/bank/{id}', [StatisticsController::class, 'getTotalByBankBank']);
        Route::get('statistics/total-banks/{id}', [StatisticsController::class, 'getTotalByBankUser']);
        /*Totalizeds*/
        Route::get('totalized/v2', [StatisticsController::class, 'getNewTotalized']);
        Route::get('totalized', [StatisticsController::class, 'getTotalized']);
        Route::get('final-balances', [StatisticsController::class, 'getFinalBalance']);
        Route::get('final-transactions', [StatisticsController::class, 'getFinalBalanceTransactions']);

        /*Inconsistences*/
        Route::get('inconsistences', [InconsistenceController::class, 'index']);
        Route::patch('inconsistences/verify/all', [InconsistenceController::class, 'verify_all']);
        Route::patch('inconsistences/verify/{id}', [InconsistenceController::class, 'verify_inconsistence']);

        /*Working Days*/
        Route::post('working-days', [WorkingDayController::class, 'store']);
    });
});
