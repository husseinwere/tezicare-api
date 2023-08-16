<?php

use App\Http\Controllers\InsuranceCoverController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientInsuranceController;
use App\Http\Controllers\UserController;
use App\Models\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/auth/login', [UserController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function() {

    Route::post('/auth/register', [UserController::class, 'register']);
    Route::post('/auth/logout', [UserController::class, 'logout']);

    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::post('/patients/create', [PatientController::class, 'store']);
    Route::put('/patients/update/{id}', [PatientController::class, 'update']);
    Route::put('/patients/delete/{id}', [PatientController::class, 'destroy']);
    Route::get('/patients/search/name/{name}', [PatientController::class, 'searchByName']);
    Route::get('/patients/search/id/{id}', [PatientController::class, 'searchById']);

    Route::get('/sessions/{id}', [PatientSession::class, 'show']);
    Route::post('/sessions/create', [PatientSession::class, 'store']);
    Route::put('/sessions/update/{id}', [PatientSession::class, 'update']);
    Route::put('/sessions/discharge/{id}', [PatientSession::class, 'discharge']);
    Route::put('/sessions/delete/{id}', [PatientSession::class, 'destroy']);

    Route::resource('insurance/patients', PatientInsuranceController::class);

    Route::resource('insurance/covers', InsuranceCoverController::class);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
