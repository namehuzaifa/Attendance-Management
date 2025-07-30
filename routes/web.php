<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth')->group(function () {
    // Employee Routes
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');

    // Admin Routes
    Route::middleware('can:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::post('/users', [AdminController::class, 'createUser'])->name('admin.users.create');
        Route::put('/users/{id}/timing', [AdminController::class, 'updateUserTiming'])->name('admin.users.timing');

        Route::get('/attendance', [AttendanceController::class, 'adminIndex'])->name('admin.attendance');
        Route::put('/attendance/{id}', [AttendanceController::class, 'editAttendance'])->name('admin.attendance.edit');

        Route::get('/leave', [LeaveController::class, 'adminIndex'])->name('admin.leave');
        Route::put('/leave/{id}/status', [LeaveController::class, 'updateStatus'])->name('admin.leave.status');

        Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
        Route::get('/off-days', [AdminController::class, 'offDays'])->name('admin.offdays');
        Route::post('/off-days', [AdminController::class, 'createOffDay'])->name('admin.offdays.create');
    });
});


require __DIR__.'/auth.php';
