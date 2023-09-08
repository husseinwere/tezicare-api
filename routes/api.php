<?php

use App\Http\Controllers\Billing\PaymentRecordController;
use App\Http\Controllers\Billing\PaymentRequestController;
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
use App\Http\Controllers\Queues\InpatientQueueController;
use App\Http\Controllers\Queues\LabQueueController;
use App\Http\Controllers\Queues\NurseQueueController;
use App\Http\Controllers\Queues\PharmacyQueueController;
use App\Http\Controllers\Queues\RadiologyQueueController;
use App\Http\Controllers\Queues\TriageQueueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Ward\BedController;
use App\Http\Controllers\Ward\WardController;
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

    //AUTH/USERS
    Route::post('/auth/register', [UserController::class, 'register']);
    Route::get('/auth/logout', [UserController::class, 'logout']);
    Route::resource('users', UserController::class);

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

    //TRIAGE QUEUE
    Route::resource('queue/triage', TriageQueueController::class);

    //DOCTOR QUEUE
    Route::resource('queue/doctor', DoctorQueueController::class);

    //NURSE QUEUE
    Route::resource('queue/nurse', NurseQueueController::class);

    //LAB QUEUE
    Route::resource('queue/lab', LabQueueController::class);

    //RADIOLOGY QUEUE
    Route::resource('queue/radiology', RadiologyQueueController::class);

    //PHARMACY QUEUE
    Route::resource('queue/pharmacy', PharmacyQueueController::class);

    //INPATIENTS QUEUE
    Route::resource('queue/inpatients', InpatientQueueController::class);

    //CLEARANCE QUEUE
    Route::resource('queue/clearance', ClearanceQueueController::class);

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

    //WARDS
    Route::resource('wards', WardController::class);

    //BEDS
    Route::resource('beds', BedController::class);
    Route::get('/beds/transfer/{id}', [BedController::class, 'transfer']);

    //PAYMENT REQUESTS
    Route::resource('payment-requests', PaymentRequestController::class);
    Route::get('/payment-requests/cancel/{id}', [PaymentRequestController::class, 'cancel']);

    //PAYMENT RECORDS
    Route::resource('payment-records', PaymentRecordController::class);
    Route::get('/payment-records/session/{id}', [PaymentRecordController::class, 'sessionRecords']);

    //PATIENT INSURANCE
    Route::resource('insurance/patients', PatientInsuranceController::class);

    //INSURANCE COVERS
    Route::resource('insurance/covers', InsuranceCoverController::class);

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
