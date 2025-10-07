<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShiftTimingController;
use App\Http\Controllers\UsersController;
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
    return redirect()->route('login');
    return view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

    // Route::get('/', [ProfileController::class, 'edit'])->name('home');

    Route::controller(UsersController::class)->group(function (){
        Route::get('user-create', 'create')->name('user-create')->middleware(['auth', 'Allow:admin']);
        Route::post('user-create', 'store')->name('user-store')->middleware(['auth', 'Allow:admin']);

        Route::get('user-edit/{id}', 'edit')->name('user-edit')->middleware(['auth', 'Allow:admin']);
        Route::post('user-update/{id}', 'update')->name('user-update')->middleware(['auth', 'Allow:admin']);

        Route::get('user-list', 'index')->name('user-list')->middleware(['auth', 'Allow:admin']);
        Route::get('user-delete/{id}', 'destroy')->name('user-delete')->middleware(['auth', 'Allow:admin']);
    });

    Route::controller(ShiftTimingController::class)->group(function (){
        Route::get('/shift-timing-list', 'index')->name('shift-list')->middleware(['auth', 'Allow:admin']);
        Route::post('/shift-timing-create', 'store')->name('shift-store')->middleware(['auth', 'Allow:admin']);
        Route::post('/shift-timing-update/{id}', 'update')->name('shift-update')->middleware(['auth', 'Allow:admin']);
        Route::get('/shift-timing-delete', 'destroy')->name('shift-delete')->middleware(['auth', 'Allow:admin']);
    });

    Route::controller(DepartmentController::class)->group(function (){
        Route::get('/department-list', 'index')->name('department-list')->middleware(['auth', 'Allow:admin']);
        Route::post('/department-create', 'store')->name('department-store')->middleware(['auth', 'Allow:admin']);
        Route::post('/department-update/{id}', 'update')->name('department-update')->middleware(['auth', 'Allow:admin']);
        Route::get('/department-delete', 'destroy')->name('department-delete')->middleware(['auth', 'Allow:admin']);
    });

    Route::controller(DesignationController::class)->group(function (){
        Route::get('/designation-list', 'index')->name('designation-list')->middleware(['auth', 'Allow:admin']);
        Route::post('/designation-create', 'store')->name('designation-store')->middleware(['auth', 'Allow:admin']);
        Route::post('/designation-update/{id}', 'update')->name('designation-update')->middleware(['auth', 'Allow:admin']);
        Route::get('/designation-delete', 'destroy')->name('designation-delete')->middleware(['auth', 'Allow:admin']);
    });

// Route::middleware('auth')->group(function () {
//     // Employee Routes
//     Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
//     Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
//     Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

//     Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
//     Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');

//     // Admin Routes
//     Route::middleware('can:admin')->prefix('admin')->group(function () {
//         Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
//         Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
//         Route::post('/users', [AdminController::class, 'createUser'])->name('admin.users.create');
//         Route::put('/users/{id}/timing', [AdminController::class, 'updateUserTiming'])->name('admin.users.timing');

//         Route::get('/attendance', [AttendanceController::class, 'adminIndex'])->name('admin.attendance');
//         Route::put('/attendance/{id}', [AttendanceController::class, 'editAttendance'])->name('admin.attendance.edit');

//         Route::get('/leave', [LeaveController::class, 'adminIndex'])->name('admin.leave');
//         Route::put('/leave/{id}/status', [LeaveController::class, 'updateStatus'])->name('admin.leave.status');

//         Route::get('/reports', [AdminController::class, 'reports'])->name('admin.reports');
//         Route::get('/off-days', [AdminController::class, 'offDays'])->name('admin.offdays');
//         Route::post('/off-days', [AdminController::class, 'createOffDay'])->name('admin.offdays.create');
//     });
// });


require __DIR__.'/auth.php';
