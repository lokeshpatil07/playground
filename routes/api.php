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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::get('/auth/google', [RegisterController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [RegisterController::class, 'handleGoogleCallback']);
Route::post('/auth/apple', [RegisterController::class, 'handleApple']);

// Turf APIs
Route::get('/turfs', [TurfController::class, 'index']);
Route::get('/turfs/nearby', [TurfController::class, 'nearby']);
Route::get('/turfs/{id}', [TurfController::class, 'show']);
Route::get('/turfs/{id}/reviews', [ReviewController::class, 'index']);


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout']);

    // Profile
    Route::get('/user', [ProfileController::class, 'show']);
    Route::put('/user/profile', [ProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | Customer Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('customer')->name('customer.')->middleware('role:customer')->group(function () {

        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::post('/bookings/verify', [BookingController::class, 'verifyPayment'])->name('bookings.verify');
        Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

        Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Owner Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('owner')->name('owner.')->middleware('role:owner')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Grounds
        Route::apiResource('grounds', GroundController::class)->names([
            'index' => 'owner.grounds.index',
            'store' => 'owner.grounds.store',
            'show' => 'owner.grounds.show',
            'update' => 'owner.grounds.update',
            'destroy' => 'owner.grounds.destroy',
        ]);

        // Extra ground actions
        Route::post('/grounds/{id}', [GroundController::class, 'update'])->name('grounds.image-upload');
        Route::delete('/grounds/{id}/remove-image', [GroundController::class, 'removeImage'])->name('grounds.remove-image');

        // Payouts
        Route::apiResource('payouts', OwnerPayoutController::class)
            ->only(['index', 'store'])
            ->names([
                'index' => 'owner.payouts.index',
                'store' => 'owner.payouts.store',
            ]);

        // Bookings
        Route::apiResource('bookings', OwnerBookingController::class)
            ->only(['index', 'show'])
            ->names([
                'index' => 'owner.bookings.index',
                'show' => 'owner.bookings.show',
            ]);
    });

    // Upgrade to Owner
    Route::post('/owner/upgrade', [OwnerRegisterController::class, 'upgrade'])->name('owner.upgrade');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {

        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Grounds
        Route::apiResource('grounds', AdminGround::class)
            ->only(['index', 'update'])
            ->names([
                'index' => 'admin.grounds.index',
                'update' => 'admin.grounds.update',
            ]);

        // Payouts
        Route::get('/payouts', [AdminPayout::class, 'index'])->name('payouts.index');
        Route::post('/payouts/{id}', [AdminPayout::class, 'update'])->name('payouts.update');

        // Users
        Route::apiResource('users', UserController::class)->names([
            'index' => 'admin.users.index',
            'store' => 'admin.users.store',
            'show' => 'admin.users.show',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);

        Route::put('/users/{id}/payment-keys', [UserController::class, 'updatePaymentKeys'])->name('users.payment-keys');

        // Settings
        Route::get('/settings', [AdminSetting::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminSetting::class, 'update'])->name('settings.update');
    });
});
