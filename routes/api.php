<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\UserController;
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

Route::post('/users/register', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function() {

    Route::post('/users/logout', [UserController::class, 'logout']);

    Route::get('/patients', [PatientController::class, 'index']);
    Route::get('/patients/{id}', [PatientController::class, 'show']);
    Route::post('/patients/create', [PatientController::class, 'store']);
    Route::put('/patients/update/{id}', [PatientController::class, 'update']);
    Route::put('/patients/delete/{id}', [PatientController::class, 'destroy']);
    Route::get('/patients/search/name/{name}', [PatientController::class, 'searchByName']);
    Route::get('/patients/search/id/{id}', [PatientController::class, 'searchById']);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
