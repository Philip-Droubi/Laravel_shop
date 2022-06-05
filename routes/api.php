<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ProdimagesController;
use App\Http\Controllers\AppManagementController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SocialiteController;

Route::group(['middleware' => ['auth:sanctum', 'lang', /*'is_verify_email'*/]], function () {
    Route::prefix("categories")->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/update/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
        Route::get('/search', [CategoryController::class, 'search']);
        Route::get('/searchsug', [CategoryController::class, 'searchsug']);
        Route::get('/list', [CategoryController::class, 'catList']);
        Route::get('/{id}', [CategoryController::class, 'show']);
    });
    Route::prefix("users")->group(function () {
        Route::get('/logout', [AuthController::class, 'logout']);
        Route::put('/update', [AuthController::class, 'update']);
        Route::get('/', [AuthController::class, 'show']);
        Route::get('/show', [AuthController::class, 'showMyProduct']);
        Route::delete('/', [AuthController::class, 'destroy']);
        Route::get('/tests', [AuthController::class, 'tests'])->middleware(['is_verify_email']);
    });
    Route::prefix("products")->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/home', [ProductController::class, 'getHomeProducts']); //???
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/show/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        Route::get('/search', [ProductController::class, 'search']);
        Route::get('/searchsug', [ProductController::class, 'searchsug']);
        Route::get('/sort', [ProductController::class, 'sort']);
    });
    Route::prefix("like")->group(function () {
        Route::get('/{id}', [LikeController::class, 'store']);
        Route::delete('/{id}', [LikeController::class, 'destroy']);
    });
    Route::prefix("sales")->group(function () {
        Route::post('/{id}', [SaleController::class, 'store']);
        Route::put('/{id}', [SaleController::class, 'update']);
        Route::delete('/{id}', [SaleController::class, 'destroy']);
    });
    Route::prefix("comment")->group(function () {
        Route::get('/{id}', [CommentController::class, 'index']);
        Route::post('/{id}', [CommentController::class, 'store']);
        Route::put('/{id}', [CommentController::class, 'update']);
        Route::delete('/{id}', [CommentController::class, 'destroy']);
    });
    Route::prefix("images")->group(function () {
        Route::get('/{id}', [ProdimagesController::class, 'index']);
        Route::post('/{id}', [ProdimagesController::class, 'store']);
        Route::put('/{id}', [ProdimagesController::class, 'update']);
        Route::post('/d/{id}', [ProdimagesController::class, 'destroy']);
    });
    Route::prefix("roles")->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::post('/giverole', [RoleController::class, 'giverole']);
        Route::get('/show/{id}', [RoleController::class, 'show']);
        Route::get('/showusers/{id}', [RoleController::class, 'showUserWithRole']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
    });
    Route::prefix("appmanage")->group(function () {
        Route::get('/users', [AppManagementController::class, 'GetAppUsersList']);
    });
    Route::post('/verify', [AuthController::class, 'verifyAccount']);
    Route::get('/getcode', [AuthController::class, 'getCode']);
});
//no need for token
Route::group(['middleware' => ['lang']], function () {
    //Auth
    Route::post('/', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    //Forget Password
    Route::post('/forgetpassword', [ForgotPasswordController::class, 'submitForgetPasswordForm']);
    Route::post('/forgetpassword/check', [ForgotPasswordController::class, 'verifytoken']);
    Route::get('reset-password/{token}', [ForgotPasswordController::class, 'verifytoken2'])->name('reset.password');
    Route::post('/forgetpassword/reset', [ForgotPasswordController::class, 'resetpassword']);
    //
    Route::post('/login/callback', [SocialiteController::class, 'handleProviderCallback']);
    //ex: URL/api/login/callback
});
