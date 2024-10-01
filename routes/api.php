<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTagsController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [UserController::class, 'Register']);
Route::post('/login', [UserController::class, 'Login']);

Route::post('/confirm-email', [UserController::class, 'checkEmail']);

Route::post('/resend-confirm-email', [UserController::class, 'resendResetCode']);


Route::get('/prodimgs/{filename}', function ($filename) {
    $path = storage_path('app/public/products/' . $filename);


    // if (!file_exists($path)) {
    //     abort(404);
    // }
    $file = file_get_contents($path);
    $type = mime_content_type($path);

    return Response::make($file, 200, [
        'Content-Type' => $type,
        // 'Content-Disposition'=> 'inline, filename="'. $filename . '"',
    ]);
})->name('prodimgs.get');



Route::post('/resend-verification', [UserController::class, 'resendVerificationCode']);
Route::post('/verify-account', [UserController::class, 'verifyUser']);



Route::group(['middleware' => ['auth:sanctum']], function () {



    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);



    Route::get('/tags', [ProductTagsController::class, 'index']);
    Route::post('/tags', [ProductTagsController::class, 'store']);
    Route::get('/tags/{id}', [ProductTagsController::class, 'show']);
    Route::post('/tags/{id}', [ProductTagsController::class, 'update']);
    Route::delete('/tags/{id}', [ProductTagsController::class, 'destroy']);






    Route::post('/user/update', [UserController::class, 'update']);

    Route::post('/logout', [UserController::class, 'Logout']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::get('/user-info', [UserController::class, 'userInfo']);
});
