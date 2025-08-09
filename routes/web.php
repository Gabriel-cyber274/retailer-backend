<?php

use App\Http\Controllers\CustomeSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/success', function () {
    return view('success');
});



Route::get('/{name}/{shop_id}', [CustomeSiteController::class, 'index']);
