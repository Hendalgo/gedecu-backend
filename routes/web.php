<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/{any?}', function ($any = null) {
    $path = $any ? public_path('frontend/dist/'.$any) : public_path('frontend/dist/index.html');

    if (file_exists($path)) {
        return response()->file($path);
    }

    return file_get_contents(public_path('frontend/dist/index.html'));
})->where('any', '.*');
