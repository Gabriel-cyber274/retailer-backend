<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductFeatureImagesController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductTagsController;
use App\Http\Controllers\RetailProductController;
use App\Http\Controllers\SavedProductController;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\UserCartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/orders', [OrderController::class, 'store']);
Route::post('/customers', [CustomerController::class, 'store']);



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

Route::post('/reset-password', [UserController::class, 'resetPassword']);




Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::get('/products', [ProductController::class, 'index']);

    Route::get('/products-instock', [ProductController::class, 'allInstock']);
    Route::get('/products-outofstock', [ProductController::class, 'allOutOfStock']);

    Route::post('/products', [ProductController::class, 'store']);

    Route::get('/products/search/{slug}', [ProductController::class, 'search']);

    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);



    Route::get('/tags', [ProductTagsController::class, 'index']);
    Route::post('/tags', [ProductTagsController::class, 'store']);
    Route::post('/tags-multiple', [ProductTagsController::class, 'storeMultiple']);

    Route::get('/tags/{id}', [ProductTagsController::class, 'show']);
    Route::post('/tags/{id}', [ProductTagsController::class, 'update']);
    Route::delete('/tags/{id}', [ProductTagsController::class, 'destroy']);



    Route::get('/customers', [CustomerController::class, 'index']);
    // Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::post('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);


    Route::get('/product-featured-images/{productId}', [ProductFeatureImagesController::class, 'index']);
    Route::post('/product-featured-images', [ProductFeatureImagesController::class, 'store']);
    Route::get('/product-featured-images/show/{id}', [ProductFeatureImagesController::class, 'show']);
    Route::post('/product-featured-images/{id}', [ProductFeatureImagesController::class, 'update']);
    Route::delete('/product-featured-images/{id}', [ProductFeatureImagesController::class, 'destroy']);



    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('/categories-attachproducts/{id}', [CategoryController::class, 'attachProductToCategory']);
    Route::post('/products-attachcategories/{id}', [CategoryController::class, 'attachCategoryToProduct']);


    Route::post('/categories-detachproducts/{id}', [CategoryController::class, 'detachProductFromCategory']);
    Route::post('/categories-attachRetailProducts/{id}', [CategoryController::class, 'attachRetailProductToCategory']);
    Route::post('/categories-detachRetailProducts/{id}', [CategoryController::class, 'detachRetailProductToCategory']);
    Route::get('/myretail-categories', [CategoryController::class, 'getMyCategoryWithRetailProduct']);
    Route::get('/myretail-categories/{catId}', [CategoryController::class, 'getSingleCategoryRetailProducts']);








    Route::post('/activate-shop', [UserController::class, 'activateShop']);
    Route::post('/deactivate-shop', [UserController::class, 'deactivateShop']);



    Route::get('/retails', [RetailProductController::class, 'index']);
    Route::post('/retails', [RetailProductController::class, 'store']);
    Route::get('/retails/{id}', [RetailProductController::class, 'show']);
    Route::post('/retails/{id}', [RetailProductController::class, 'update']);
    Route::delete('/retails/{id}', [RetailProductController::class, 'destroy']);


    Route::get('/orders-allPending', [OrderController::class, 'allPending']);
    Route::get('/orders-allCompleted', [OrderController::class, 'allCompleted']);
    Route::get('/orders-allCancelled', [OrderController::class, 'allCancelled']);


    Route::get('/amountmade-alltime', [OrderController::class, 'getMoneyMadeAllTime']);
    Route::get('/amountmade-monthly', [OrderController::class, 'getMoneyMonthly']);
    Route::get('/amountmade-weekly', [OrderController::class, 'getMoneyWeekly']);
    Route::get('/amountmade-daily', [OrderController::class, 'getMoneyDaily']);



    Route::get('/orders-all', [OrderController::class, 'all']);
    Route::get('/orders-allMonthly', [OrderController::class, 'getMonthlyOrders']);
    Route::get('/orders-allYearly', [OrderController::class, 'getYearlyOrders']);
    Route::get('/orders-allDaily', [OrderController::class, 'getDailyOrders']);
    Route::get('/orders-allWeekly', [OrderController::class, 'getWeeklyOrders']);
    Route::get('/orders-allcreatedMonthly', [OrderController::class, 'ordersCreatedMonthly']);


    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders-direct', [OrderController::class, 'directOrders']);
    Route::get('/orders-customer', [OrderController::class, 'customerOrders']);

    
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);




    Route::get('/deposits', [DepositController::class, 'index']);
    Route::get('/deposits-all', [DepositController::class, 'all']);
    Route::get('/deposits-allYear', [DepositController::class, 'allThisYear']);
    Route::get('/deposits-allMonth', [DepositController::class, 'allThisMonth']);
    Route::get('/deposits-allWeek', [DepositController::class, 'allThisWeek']);
    Route::get('/deposits-allToday', [DepositController::class, 'allToday']);

    Route::get('/deposits-allPending', [DepositController::class, 'getAllPending']);
    Route::get('/deposits-allCompleted', [DepositController::class, 'getAllCompleted']);
    Route::get('/deposits-allCancelled', [DepositController::class, 'getAllCancelled']);

    Route::get('/deposits-allCreatedMonthly', [DepositController::class, 'depositsCreatedMonthly']);



    Route::post('/deposits', [DepositController::class, 'store']);
    Route::get('/deposits/{id}', [DepositController::class, 'show']);
    Route::post('/deposits/{id}', [DepositController::class, 'update']);
    Route::delete('/deposits/{id}', [DepositController::class, 'destroy']);






    Route::get('users/all-time', [UserController::class, 'allTimeUsers']);

    Route::get('users/top-reseller', [UserController::class, 'topResellers']);

    Route::get('users/this-year', [UserController::class, 'usersThisYear']);

    Route::get('users/this-month', [UserController::class, 'usersThisMonth']);

    Route::get('users/this-week', [UserController::class, 'usersThisWeek']);

    Route::get('users/today', [UserController::class, 'usersToday']);

    Route::get('users/monthly-onboarding', [UserController::class, 'usersOnboardedMonthly']);


    //saved products
    Route::get('/saved-products', [SavedProductController::class, 'index']);
    Route::post('/saved-products', [SavedProductController::class, 'store']);
    Route::get('/saved-products/{id}', [SavedProductController::class, 'show']);
    Route::delete('/saved-products/{id}', [SavedProductController::class, 'destroy']);
    Route::post('/saved-products/toggle', [SavedProductController::class, 'toggle']);

    // rating
    Route::apiResource('product-reviews', ProductReviewController::class);
    Route::get('/review-by-product/{id}', [ProductReviewController::class, 'productReview']);

    //cart
    Route::get('/cart', [UserCartController::class, 'index']);
    Route::post('/cart', [UserCartController::class, 'store']);
    Route::get('/cart/{userCart}', [UserCartController::class, 'show']);
    Route::post('/cart/{userCart}', [UserCartController::class, 'update']);
    Route::delete('/cart/{userCart}', [UserCartController::class, 'destroy']);

    //addresses
    Route::get('/user-addresses', [UserAddressController::class, 'index']);
    Route::post('/user-addresses', [UserAddressController::class, 'store']);
    Route::get('/user-addresses/{userAddress}', [UserAddressController::class, 'show']);
    Route::post('/user-addresses/{userAddress}', [UserAddressController::class, 'update']);
    Route::delete('/user-addresses/{userAddress}', [UserAddressController::class, 'destroy']);


    //withdrawal 
    Route::apiResource('withdrawals', WithdrawalController::class);










    Route::post('/user/update', [UserController::class, 'update']);

    Route::post('/logout', [UserController::class, 'Logout']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::get('/user-info', [UserController::class, 'userInfo']);
});
