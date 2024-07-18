<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// General Dashboard
Route::middleware(['auth', 'verified'])
    ->get('/dashboard', [DashboardController::class, 'redirectToDashboard'])->name('dashboard');

// Laboratory Management
Route::middleware(['auth', 'verified'])
    ->prefix('laboratories')
    ->group(function () {
        Route::get('/', [LaboratoryController::class, 'viewLaboratories'])->name('laboratories');
    });

// User Management
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('users')
    ->group(function () {
        Route::get('/', [UserController::class, 'viewUsers'])->name('users');
        Route::delete('/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulkDelete');

    });
    
// General Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
