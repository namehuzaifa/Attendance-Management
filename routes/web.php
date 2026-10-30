<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\IpAddressController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ShiftTimingController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('login'))->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // The /profile routes that used to sit here pointed at a ProfileController
    // that was never written; the portal edits a profile through user-edit.

    // ---------------------------------------------------------- attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

    // --------------------------------------------------------------- leave
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::post('/leave/{id}/cancel', [LeaveController::class, 'cancel'])->name('leave.cancel');

    // Own profile only; the controller rejects anyone editing another id.
    Route::get('user-edit/{id}', [UsersController::class, 'edit'])->name('user-edit');
    Route::post('user-update/{id}', [UsersController::class, 'update'])->name('user-update');
});

Route::middleware(['auth', 'Allow:admin'])->group(function () {
    // --------------------------------------------------------------- users
    Route::controller(UsersController::class)->group(function () {
        Route::get('user-list', 'index')->name('user-list');
        Route::get('user-create', 'create')->name('user-create');
        Route::post('user-create', 'store')->name('user-store');
        // Was a GET link, which meant any crafted URL an admin opened could
        // delete an employee.
        Route::delete('user-delete/{id}', 'destroy')->name('user-delete');
    });

    // -------------------------------------------------------------- shifts
    Route::controller(ShiftTimingController::class)->group(function () {
        Route::get('/shift-timing-list', 'index')->name('shift-list');
        Route::post('/shift-timing-create', 'store')->name('shift-store');
        Route::post('/shift-timing-update/{id}', 'update')->name('shift-update');
        Route::post('/shift-timing-delete', 'destroy')->name('shift-delete');
    });

    // --------------------------------------------------------- departments
    Route::controller(DepartmentController::class)->group(function () {
        Route::get('/department-list', 'index')->name('department-list');
        Route::post('/department-create', 'store')->name('department-store');
        Route::post('/department-update/{id}', 'update')->name('department-update');
        Route::post('/department-delete', 'destroy')->name('department-delete');
    });

    // -------------------------------------------------------- designations
    Route::controller(DesignationController::class)->group(function () {
        Route::get('/designation-list', 'index')->name('designation-list');
        Route::post('/designation-create', 'store')->name('designation-store');
        Route::post('/designation-update/{id}', 'update')->name('designation-update');
        Route::post('/designation-delete', 'destroy')->name('designation-delete');
    });

    // ------------------------------------------------------- ip restriction
    Route::controller(IpAddressController::class)->group(function () {
        Route::get('/ip-address-list', 'index')->name('ip-list');
        Route::post('/ip-address-create', 'store')->name('ip-store');
        Route::post('/ip-address-update/{id}', 'update')->name('ip-update');
        Route::post('/ip-address-delete', 'destroy')->name('ip-delete');
        Route::post('/ip-restriction-toggle', 'toggleSetting')->name('ip-toggle');
    });

    // ------------------------------------------------------------ holidays
    Route::controller(HolidayController::class)->group(function () {
        Route::get('/holiday-list', 'index')->name('holiday-list');
        Route::post('/holiday-create', 'store')->name('holiday-store');
        Route::post('/holiday-update/{id}', 'update')->name('holiday-update');
        Route::post('/holiday-delete', 'destroy')->name('holiday-delete');
    });

    // --------------------------------------------------------- leave admin
    Route::get('/admin/leave', [LeaveController::class, 'adminIndex'])->name('admin.leave');
    Route::post('/admin/leave/{id}/status', [LeaveController::class, 'updateStatus'])->name('admin.leave.status');

    // ------------------------------------------------------------- reports
    Route::get('/admin/attendance-reports', [AttendanceController::class, 'adminReports'])->name('admin.reports');
    Route::put('/attendance/{id}', [AttendanceController::class, 'updateAttendance'])->name('attendance.update');

    Route::get('/monthly/user-report', [AttendanceController::class, 'userMonthlyReport'])->name('monthly.user-report');
    Route::get('/monthly/all-users-report', [AttendanceController::class, 'allUsersMonthlyReport'])->name('monthly.all-users-report');
    Route::get('/monthly/export', [AttendanceController::class, 'exportMonthly'])->name('monthly.export');
});

require __DIR__ . '/auth.php';
