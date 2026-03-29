<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Customer\BookingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Customer\TurfController;
use App\Http\Controllers\Api\Customer\ReviewController;
use App\Http\Controllers\Api\Owner\DashboardController;
use App\Http\Controllers\Api\Owner\GroundController;
use App\Http\Controllers\Api\Owner\PayoutController as OwnerPayoutController;
use App\Http\Controllers\Api\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Api\Owner\RegisterController as OwnerRegisterController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Api\Admin\GroundController as AdminGround;
use App\Http\Controllers\Api\Admin\PayoutController as AdminPayout;
use App\Http\Controllers\Api\Admin\SettingController as AdminSetting;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/auth/google', [RegisterController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [RegisterController::class, 'handleGoogleCallback']);
Route::post('/auth/apple', [RegisterController::class, 'handleApple']);

Route::get('/turfs', [TurfController::class, 'index']);
Route::get('/turfs/nearby', [TurfController::class, 'nearby']);
Route::get('/turfs/{id}', [TurfController::class, 'show']);
Route::get('/turfs/{id}/reviews', [ReviewController::class, 'index']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    
    // Unified Profile
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user/profile', [ProfileController::class, 'update']);
    
    // Customer
    Route::prefix('customer')->middleware('role:customer')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/bookings/verify', [BookingController::class, 'verifyPayment']);
        Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
        Route::post('/reviews', [ReviewController::class, 'store']);
    });

    // Owner
    Route::prefix('owner')->middleware('role:owner')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::apiResource('grounds', GroundController::class);
        Route::post('/grounds/{id}', [GroundController::class, 'update']); // For image uploads
        Route::delete('/grounds/{id}/remove-image', [GroundController::class, 'removeImage']);
        Route::apiResource('payouts', OwnerPayoutController::class)->only(['index', 'store']);
        Route::apiResource('bookings', OwnerBookingController::class)->only(['index', 'show']);
    });

    Route::post('/owner/upgrade', [OwnerRegisterController::class, 'upgrade']);

    // Admin hello
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index']);
        Route::apiResource('grounds', AdminGround::class)->only(['index', 'update']);
        Route::get('/payouts', [AdminPayout::class, 'index']);
        Route::post('/payouts/{id}', [AdminPayout::class, 'update']);
        Route::apiResource('users', UserController::class);
        Route::put('/users/{id}/payment-keys', [UserController::class, 'updatePaymentKeys']);
        Route::get('/settings', [AdminSetting::class, 'index']);
        Route::put('/settings', [AdminSetting::class, 'update']);
    });
});
