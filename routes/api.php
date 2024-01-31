<?php

use App\Http\Controllers\AccountTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DuplicatedReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportTypeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::middleware('auth.veryfied')->group(function (){
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
    Route::post('bank-accounts', [BankAccountController::class, 'store']);
    Route::put('bank-accounts/{id}', [BankAccountController::class, 'update']);
    Route::delete('bank-accounts/{id}', [BankAccountController::class, 'destroy']);
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
    Route::post('reports', [ReportController::class, 'store']);
    Route::put('reports/{id}', [ReportController::class, 'update']);
    Route::delete('reports/{id}', [ReportController::class, 'destroy']);
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

    /*Stadistics*/
    Route::get('stadistics', [ReportController::class, 'getMovementsByPeriods']);

});