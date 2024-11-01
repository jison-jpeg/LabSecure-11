<?php

use App\Http\Controllers\Auth\GoogleAuthController;
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

// Login
Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// Home
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect()->route('login'); // Redirect to the login route
});

// General Dashboard
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson,instructor,student'])
    ->get('/dashboard', [DashboardController::class, 'redirectToDashboard'])->name('dashboard');

// Laboratory Management
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson,instructor'])
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
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson,instructor,student'])
    ->prefix('attendances')
    ->group(function () {
        Route::get('/', [AttendanceController::class, 'viewAttendance'])->name('attendance');
        Route::get('/subject/{schedule}', [AttendanceController::class, 'viewSubjectAttendance'])->name('attendance.subject.view');
        Route::get('/user/{user}', [AttendanceController::class, 'viewUserAttendance'])->name('attendance.user.view');
    });

// Faculty Management
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson'])
    ->prefix('faculties')
    ->group(function () {
        Route::get('/', [FacultyController::class, 'viewFaculties'])->name('faculties');
        Route::get('/{faculty}', [FacultyController::class, 'viewFaculty'])->name('faculty.view');
    });

// Student Management
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson,instructor'])
    ->prefix('students')
    ->group(function () {
        Route::get('/', [StudentController::class, 'viewStudents'])->name('students');
        Route::get('/{student}', [StudentController::class, 'viewStudent'])->name('student.view');
    });

// Subject Management
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson,instructor,student'])
    ->prefix('subjects')
    ->group(function () {
        Route::get('/', [SubjectController::class, 'viewSubjects'])->name('subjects');
        Route::get('/{subject}', [SubjectController::class, 'viewSubject'])->name('subject.view');
    });

// Courses Management
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson'])
    ->prefix('courses')
    ->group(function () {
        Route::get('/', [CollegeManagementController::class, 'viewCollegeManagement'])->name('college-management');
        Route::get('/college/{college}', [CollegeManagementController::class, 'viewCollege'])->name('college.view');
        Route::get('/department/{department}', [CollegeManagementController::class, 'viewDepartment'])->name('department.view');
    });

// Schedule
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson'])
    ->prefix('schedules')
    ->group(function () {
        Route::get('/', [ScheduleController::class, 'viewSchedules'])->name('schedule');
        Route::get('/{schedule}', [ScheduleController::class, 'viewSchedule'])->name('schedule.view');

    });

// Classes
Route::middleware(['auth', 'verified', 'role:instructor,student'])
    ->prefix('classes')
    ->group(function () {
        Route::get('/', [ClassController::class, 'viewClasses'])->name('classes');
        Route::get('/{schedule}', [ClassController::class, 'viewClass'])->name('class.view');
    });


// Section
Route::middleware(['auth', 'verified', 'role:admin,dean,chairperson'])
    ->prefix('sections')
    ->group(function () {
        Route::get('/', [SectionController::class, 'viewSections'])->name('sections');
        Route::get('/{section}', [SectionController::class, 'viewSection'])->name('section.view');
    });


// General Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile-picture', [ProfileController::class, 'updatePicture'])->name('profile.update-picture');
    Route::delete('/profile-picture', [ProfileController::class, 'removeProfilePicture'])->name('profile.remove-picture');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Transaction Logs
Route::middleware(['auth', 'verified', 'role:admin'])
    ->get('/logs', [TransactionLogController::class, 'viewTransactionLog'])->name('transaction-logs');

require __DIR__ . '/auth.php';
