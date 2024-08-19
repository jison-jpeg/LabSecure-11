<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\LaboratoryController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Login
Route::post('/login', [AuthController::class, 'login']);

// Laboratory Access
Route::post('/laboratory/access', [LaboratoryController::class, 'handleLaboratoryAccess']);
