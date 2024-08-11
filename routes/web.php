<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\CollegeManagementController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
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
    });

// Faculty Management
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('faculties')
    ->group(function () {
        Route::get('/', [FacultyController::class, 'viewFaculties'])->name('faculties');
    });

// Student Management
Route::middleware(['auth', 'verified'])
    ->prefix('students')
    ->group(function () {
        Route::get('/', [StudentController::class, 'viewStudent'])->name('students');
    });

// Subject Management
Route::middleware(['auth', 'verified'])
    ->prefix('subjects')
    ->group(function () {
        Route::get('/', [SubjectController::class, 'viewSubject'])->name('subjects');
    });


// Attendance Management
Route::middleware(['auth', 'verified'])
    ->prefix('attendances')
    ->group(function () {
        Route::get('/', [AttendanceController::class, 'viewAttendance'])->name('attendance');
        Route::post('/', [AttendanceController::class, 'store'])->name('attendance.store');
    });

// Courses Management
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('courses')
    ->group(function () {
        Route::get('/', [CollegeManagementController::class, 'viewCollegeManagement'])->name('college-management');
    });

// Schedule
Route::middleware(['auth'])
    ->prefix('schedules')
    ->group(function () {
        Route::get('/', [ScheduleController::class, 'viewSchedule'])->name('schedule');
    });

// Classes
Route::middleware('auth')
    ->prefix('classes')
    ->group(function () {
        Route::get('/', [ClassController::class, 'viewClasses'])->name('classes');
        Route::get('/{section}', [ClassController::class, 'viewSection'])->name('viewSection');
    });


// Section
Route::middleware('auth')
    ->prefix('sections')
    ->group(function () {
        Route::get('/', [SectionController::class, 'viewSection'])->name('sections');
    });


// General Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
