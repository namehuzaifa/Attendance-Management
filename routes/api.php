<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Middleware\RefreshApiToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Consumed by the HRMS Android app. Accounts are created by an administrator
| inside the portal, so there is deliberately no public registration here.
|
*/

Route::fallback(fn () => response()->json([
    'status'  => false,
    'message' => 'API route not found.',
], 404));

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-password', [AuthController::class, 'verifyAndReset']);
});

Route::middleware(['auth:sanctum', RefreshApiToken::class])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/reset-password', [AuthController::class, 'changePassword']);
    Route::post('/profile', [ProfileController::class, 'update']);

    Route::prefix('attendance')->group(function () {
        Route::get('/home', [AttendanceController::class, 'home']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/month', [AttendanceController::class, 'month']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    });

    Route::get('/holidays', [AttendanceController::class, 'holidays']);

    Route::prefix('leaves')->group(function () {
        Route::get('/', [LeaveController::class, 'index']);
        Route::post('/', [LeaveController::class, 'store']);
        Route::post('/{id}/cancel', [LeaveController::class, 'cancel']);
    });

    // ------------------------------------------------------------- admin
    Route::middleware('api.admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/employees', [AdminController::class, 'employees']);
        Route::get('/employees/{id}/report', [AdminController::class, 'employeeReport']);
        Route::get('/attendance', [AdminController::class, 'attendance']);
        Route::get('/leaves', [AdminController::class, 'leaves']);
        Route::post('/leaves/{id}/status', [AdminController::class, 'decideLeave']);
    });
});
