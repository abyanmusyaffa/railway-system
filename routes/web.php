<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AScheduleController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingMonitorController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ExecutivePanelController;
use App\Http\Controllers\GateScanController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TrainController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// Route::inertia('/', 'Welcome')->name('home');
// routes/web.php
Route::get('/', [ScheduleController::class, 'index'])->name('public.schedules');

Route::get('/booking', [BookingController::class, 'index'])->name('booking.index');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

// routes/web.php
Route::middleware(['auth', 'role:PASSENGER'])->group(function () {
    Route::get('/my/bookings', [HistoryController::class, 'index'])->name('passenger.history');
    Route::get('/my/profile', [ProfileController::class, 'edit'])->name('passenger.profile');
    Route::post('/my/profile', [ProfileController::class, 'update'])->name('passenger.profile.update');
    Route::get('/my/bookings/{booking}/ticket', [TicketController::class, 'show'])->name('passenger.ticket');
});

// routes/web.php
Route::middleware(['auth', 'role:OFFICER'])->prefix('officer')->name('officer.')->group(function () {
    Route::get('/gate-scan', [GateScanController::class, 'index'])->name('officer.gatescan');
    Route::post('/gate-scan', [GateScanController::class, 'store'])->name('officer.gatescan.store');
    Route::get('/monitor', [BookingMonitorController::class, 'index'])->name('monitor');
    Route::patch('/monitor/{booking}/complete', [BookingMonitorController::class, 'complete'])->name('monitor.complete');
    Route::get('/executive', [ExecutivePanelController::class, 'index'])->name('executive');
    Route::post('/archive', [ArchiveController::class, 'run'])->name('archive');
});

// routes/web.php
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/trains', [TrainController::class, 'index'])->name('trains.index');
    Route::post('/trains', [TrainController::class, 'store'])->name('trains.store');
    Route::put('/trains/{train}', [TrainController::class, 'update'])->name('trains.update');
    Route::delete('/trains/{train}', [TrainController::class, 'destroy'])->name('trains.destroy');

    Route::get('/schedules', [AScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [AScheduleController::class, 'store'])->name('schedules.store');
    Route::put('/schedules/{schedule}', [AScheduleController::class, 'update'])->name('schedules.update');
    Route::delete('/schedules/{schedule}', [AScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('/passengers', [PassengerController::class, 'index'])->name('passengers.index');
    Route::post('/passengers', [PassengerController::class, 'store'])->name('passengers.store');
    Route::put('/passengers/{passenger}', [PassengerController::class, 'update'])->name('passengers.update');
    Route::delete('/passengers/{passenger}', [PassengerController::class, 'destroy'])->name('passengers.destroy');

    Route::get('/report', [ReportController::class, 'index'])->name('report');
    Route::get('/discount', [DiscountController::class, 'index'])->name('discount.index');
    Route::post('/discount', [DiscountController::class, 'calculate'])->name('discount.calculate');

    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');

});

// routes/web.php
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});