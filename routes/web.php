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
use App\Http\Controllers\TransactionLogController;
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
        Route::get('/{laboratory}', [LaboratoryController::class, 'viewLaboratory'])->name('laboratory.view');
    });

// User Management
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('users')
    ->group(function () {
        Route::get('/', [UserController::class, 'viewUsers'])->name('users');
        Route::get('/{user}', action: [UserController::class, 'viewUser'])->name('user.view');
    });

// Attendance Management
Route::middleware(['auth', 'verified'])
    ->prefix('attendances')
    ->group(function () {
        Route::get('/', [AttendanceController::class, 'viewAttendance'])->name('attendance');
        Route::get('/user/{user}', [AttendanceController::class, 'viewUserAttendance'])->name('attendance.user.view');
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
        Route::get('/', [StudentController::class, 'viewStudents'])->name('students');
        Route::get('/{student}', [StudentController::class, 'viewStudent'])->name('student.view');
    });

// Subject Management
Route::middleware(['auth', 'verified'])
    ->prefix('subjects')
    ->group(function () {
        Route::get('/', [SubjectController::class, 'viewSubjects'])->name('subjects');
        Route::get('/{subject}', [SubjectController::class, 'viewSubject'])->name('subject.view');
    });

// Courses Management
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('courses')
    ->group(function () {
        Route::get('/', [CollegeManagementController::class, 'viewCollegeManagement'])->name('college-management');
        Route::get('/college/{college}', [CollegeManagementController::class, 'viewCollege'])->name('college.view');
        Route::get('/department/{department}', [CollegeManagementController::class, 'viewDepartment'])->name('department.view');
    });

// Schedule
Route::middleware(['auth'])
    ->prefix('schedules')
    ->group(function () {
        Route::get('/', [ScheduleController::class, 'viewSchedules'])->name('schedule');
        Route::get('/{schedule}', [ScheduleController::class, 'viewSchedule'])->name('schedule.view');

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
        Route::get('/', [SectionController::class, 'viewSections'])->name('sections');
        Route::get('/{section}', [SectionController::class, 'viewSection'])->name('section.view');
    });


// General Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Transaction Logs
Route::middleware(['auth', 'verified'])
    ->get('/logs', [TransactionLogController::class, 'viewTransactionLog'])->name('transaction-logs');

require __DIR__ . '/auth.php';
