<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationRequestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('vacations.calendar');
    Route::get('/vacations', [VacationRequestController::class, 'index'])->name('vacations.index');
    Route::get('/vacations/approvals', [VacationRequestController::class, 'approvals'])->name('vacations.approvals');
    Route::post('/vacations', [VacationRequestController::class, 'store'])->name('vacations.store');
    Route::put('/vacations/{vacationRequest}', [VacationRequestController::class, 'update'])->name('vacations.update');
    Route::post('/vacations/{vacationRequest}/approve', [VacationRequestController::class, 'approve'])->name('vacations.approve');
    Route::delete('/vacations/{vacationRequest}', [VacationRequestController::class, 'destroy'])->name('vacations.destroy');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
});
