<?php

use App\Http\Controllers\InsuranceCoverController;
use App\Http\Controllers\Inventory\NonPharmaceuticalController;
use App\Http\Controllers\Inventory\PharmaceuticalController;
use App\Http\Controllers\Lab\LabTestController;
use App\Http\Controllers\Lab\RadiologyTestController;
use App\Http\Controllers\Nurse\NursingServiceController;
use App\Http\Controllers\Patient\NurseInstructionController;
use App\Http\Controllers\Patient\PatientDiagnosisController;
use App\Http\Controllers\Patient\PatientDrugController;
use App\Http\Controllers\Patient\PatientImpressionController;
use App\Http\Controllers\Patient\PatientNursingController;
use App\Http\Controllers\Patient\PatientPrescriptionController;
use App\Http\Controllers\Patient\PatientRecommendationController;
use App\Http\Controllers\Patient\PatientSymptomController;
use App\Http\Controllers\Patient\PatientTestController;
use App\Http\Controllers\Patient\PatientVitalsController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientInsuranceController;
use App\Http\Controllers\PatientSessionController;
use App\Http\Controllers\Queues\ClearanceQueueController;
use App\Http\Controllers\Queues\DoctorQueueController;
use App\Http\Controllers\Queues\LabQueueController;
use App\Http\Controllers\Queues\NurseQueueController;
use App\Http\Controllers\Queues\PharmacyQueueController;
use App\Http\Controllers\Queues\RadiologyQueueController;
use App\Http\Controllers\Queues\TriageQueueController;
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

    Route::get('/sessions/{id}', [PatientSessionController::class, 'show']);
    Route::post('/sessions/create', [PatientSessionController::class, 'store']);
    Route::put('/sessions/update/{id}', [PatientSessionController::class, 'update']);
    Route::put('/sessions/discharge/{id}', [PatientSessionController::class, 'discharge']);
    Route::put('/sessions/delete/{id}', [PatientSessionController::class, 'destroy']);

    //QUEUES
    Route::get('/queue/triage', [TriageQueueController::class, 'index']);
    Route::get('/queue/triage/{id}', [TriageQueueController::class, 'show']);
    Route::post('/queue/triage', [TriageQueueController::class, 'store']);
    Route::put('/queue/triage/update/{id}', [TriageQueueController::class, 'update']);
    Route::delete('/queue/triage/{id}', [TriageQueueController::class, 'destroy']);

    Route::get('/queue/doctor', [DoctorQueueController::class, 'index']);
    Route::get('/queue/doctor/{id}', [DoctorQueueController::class, 'show']);
    Route::post('/queue/doctor', [DoctorQueueController::class, 'store']);
    Route::put('/queue/doctor/update/{id}', [DoctorQueueController::class, 'update']);
    Route::delete('/queue/doctor/{id}', [DoctorQueueController::class, 'destroy']);

    Route::get('/queue/nurse', [NurseQueueController::class, 'index']);
    Route::get('/queue/nurse/{id}', [NurseQueueController::class, 'show']);
    Route::post('/queue/nurse', [NurseQueueController::class, 'store']);
    Route::put('/queue/nurse/update/{id}', [NurseQueueController::class, 'update']);
    Route::delete('/queue/nurse/{id}', [NurseQueueController::class, 'destroy']);

    Route::get('/queue/lab', [LabQueueController::class, 'index']);
    Route::get('/queue/lab/{id}', [LabQueueController::class, 'show']);
    Route::post('/queue/lab', [LabQueueController::class, 'store']);
    Route::put('/queue/lab/update/{id}', [LabQueueController::class, 'update']);
    Route::delete('/queue/lab/{id}', [LabQueueController::class, 'destroy']);

    Route::get('/queue/radiology', [RadiologyQueueController::class, 'index']);
    Route::get('/queue/radiology/{id}', [RadiologyQueueController::class, 'show']);
    Route::post('/queue/radiology', [RadiologyQueueController::class, 'store']);
    Route::put('/queue/radiology/update/{id}', [RadiologyQueueController::class, 'update']);
    Route::delete('/queue/radiology/{id}', [RadiologyQueueController::class, 'destroy']);

    Route::get('/queue/pharmacy', [PharmacyQueueController::class, 'index']);
    Route::get('/queue/pharmacy/{id}', [PharmacyQueueController::class, 'show']);
    Route::post('/queue/pharmacy', [PharmacyQueueController::class, 'store']);
    Route::put('/queue/pharmacy/update/{id}', [PharmacyQueueController::class, 'update']);
    Route::delete('/queue/pharmacy/{id}', [PharmacyQueueController::class, 'destroy']);

    Route::get('/queue/clearance', [ClearanceQueueController::class, 'index']);
    Route::get('/queue/clearance/{id}', [ClearanceQueueController::class, 'show']);
    Route::post('/queue/clearance', [ClearanceQueueController::class, 'store']);
    Route::put('/queue/clearance/update/{id}', [ClearanceQueueController::class, 'update']);
    Route::delete('/queue/clearance/{id}', [ClearanceQueueController::class, 'destroy']);
    //QUEUES END

    //PATIENT VITALS
    Route::resource('patient-vitals', PatientVitalsController::class);

    //PATIENT CONDITIONS
    Route::resource('patient-conditions', PatientConditionController::class);

    //PATIENT DIAGNOSIS
    Route::resource('patient-diagnosis', PatientDiagnosisController::class);

    //PATIENT IMPRESSIONS
    Route::resource('patient-impressions', PatientImpressionController::class);

    //PATIENT RECOMMENDATIONS
    Route::resource('patient-recommendations', PatientRecommendationController::class);

    //PATIENT SYMPTOMS
    Route::resource('patient-symptoms', PatientSymptomController::class);

    //NURSE INSTRUCTIONS
    Route::resource('nurse-instructions', NurseInstructionController::class);

    //PATIENT TESTS
    Route::resource('patient-tests', PatientTestController::class);

    //PATIENT PRESCRIPTIONS
    Route::resource('patient-prescriptions', PatientPrescriptionController::class);

    //PATIENT DRUGS
    Route::resource('patient-drugs', PatientDrugController::class);

    //PATIENT NURSING
    Route::resource('patient-nursing', PatientNursingController::class);

    //LAB TESTS
    Route::resource('lab-tests', LabTestController::class);

    //RADIOLOGY TESTS
    Route::resource('radiology-tests', RadiologyTestController::class);

    //RADIOLOGY TESTS
    Route::resource('nursing-services', NursingServiceController::class);

    //PHARMACEUTICALS
    Route::resource('pharmaceuticals', PharmaceuticalController::class);
    Route::get('/pharmaceuticals/search/{name}', [PharmaceuticalController::class, 'search']);

    //NON-PHARMACEUTICALS
    Route::resource('non-pharmaceuticals', NonPharmaceuticalController::class);
    Route::get('/non-pharmaceuticals/search/{name}', [NonPharmaceuticalController::class, 'search']);

    //PATIENT INSURANCE
    Route::resource('insurance/patients', PatientInsuranceController::class);

    //INSURANCE COVERS
    Route::resource('insurance/covers', InsuranceCoverController::class);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
