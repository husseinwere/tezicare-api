<?php

use App\Http\Controllers\Billing\PaymentRecordController;
use App\Http\Controllers\Billing\PaymentRequestController;
use App\Http\Controllers\Hospital\ConsultationTypeController;
use App\Http\Controllers\InsuranceCoverController;
use App\Http\Controllers\Inventory\NonPharmaceuticalController;
use App\Http\Controllers\Inventory\PharmaceuticalController;
use App\Http\Controllers\Lab\LabResultController;
use App\Http\Controllers\Lab\LabTestController;
use App\Http\Controllers\Nurse\NursingServiceController;
use App\Http\Controllers\Patient\NurseInstructionController;
use App\Http\Controllers\Patient\PatientDiagnosisController;
use App\Http\Controllers\Patient\PatientDrugController;
use App\Http\Controllers\Patient\PatientImpressionController;
use App\Http\Controllers\Patient\PatientNonPharmaceuticalController;
use App\Http\Controllers\Patient\PatientNursingController;
use App\Http\Controllers\Patient\PatientPrescriptionController;
use App\Http\Controllers\Patient\PatientRecommendationController;
use App\Http\Controllers\Patient\PatientSymptomController;
use App\Http\Controllers\Patient\PatientTestController;
use App\Http\Controllers\Patient\PatientVitalsController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientInsuranceController;
use App\Http\Controllers\PatientSessionController;
use App\Http\Controllers\Queues\AdmissionQueueController;
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
    Route::get('/auth/logout', [UserController::class, 'logout']);
    Route::resource('users', UserController::class);

    //PATIENTS
    Route::resource('patients', PatientController::class);

    //PATIENT SESSIONS
    Route::resource('sessions', PatientSessionController::class);
    Route::get('/sessions/patient-stats/{patient_id}', [PatientSessionController::class, 'getPatientStats']);
    Route::get('/sessions/discharge/{id}', [PatientSessionController::class, 'discharge']);

    //TRIAGE QUEUE
    Route::resource('queue/triage', TriageQueueController::class);
    Route::get('/queue/triage/send-to-doctor/{session_id}', [TriageQueueController::class, 'sendToDoctor']);

    //DOCTOR QUEUE
    Route::resource('queue/doctor', DoctorQueueController::class);
    Route::get('/queue/doctor/complete-session/{sessionId}', [DoctorQueueController::class, 'completeSession']);

    //NURSE QUEUE
    Route::resource('queue/nurse', NurseQueueController::class);
    Route::get('/queue/nurse/complete-session/{sessionId}', [NurseQueueController::class, 'completeSession']);

    //LAB QUEUE
    Route::resource('queue/lab', LabQueueController::class);
    Route::get('/queue/lab/complete-session/{sessionId}', [LabQueueController::class, 'completeSession']);

    //RADIOLOGY QUEUE
    Route::resource('queue/radiology', RadiologyQueueController::class);
    Route::get('/queue/radiology/complete-session/{sessionId}', [RadiologyQueueController::class, 'completeSession']);

    //PHARMACY QUEUE
    Route::resource('queue/pharmacy', PharmacyQueueController::class);
    Route::get('/queue/pharmacy/complete-session/{sessionId}', [PharmacyQueueController::class, 'completeSession']);

    //INPATIENTS QUEUE
    Route::resource('queue/inpatients', InpatientQueueController::class);

    //CLEARANCE QUEUE
    Route::resource('queue/clearance', ClearanceQueueController::class);

    //ADMISSION QUEUE
    Route::resource('queue/admission', AdmissionQueueController::class);

    //PATIENT VITALS
    Route::resource('patient-vitals', PatientVitalsController::class);
    Route::get('/patient-vitals/latest/{patient_id}', [PatientVitalsController::class, 'getLatestVitals']);

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
    Route::resource('lab-results', LabResultController::class);

    //PATIENT PRESCRIPTION
    Route::resource('patient-prescription', PatientPrescriptionController::class);

    //PATIENT DRUGS
    Route::resource('patient-drugs', PatientDrugController::class);

    //PATIENT NON-PHARMACEUTICALS
    Route::resource('patient-non-pharmaceuticals', PatientNonPharmaceuticalController::class);

    //PATIENT NURSING
    Route::resource('patient-nursing', PatientNursingController::class);

    //LAB TESTS
    Route::resource('lab-tests', LabTestController::class);

    //NURSING SERVICES
    Route::resource('nursing-services', NursingServiceController::class);

    //PHARMACEUTICALS
    Route::resource('inventory/pharmaceuticals', PharmaceuticalController::class);

    //NON-PHARMACEUTICALS
    Route::resource('inventory/non-pharmaceuticals', NonPharmaceuticalController::class);

    //WARDS
    Route::resource('wards', WardController::class);

    //BEDS
    Route::resource('beds', BedController::class);
    Route::get('/beds/transfer/{id}', [BedController::class, 'transfer']);

    //CONSULTATION TYPES
    Route::resource('consultation-types', ConsultationTypeController::class);

    //PAYMENT REQUESTS
    Route::resource('payment-requests', PaymentRequestController::class);
    Route::delete('/payment-requests/cancel/{id}', [PaymentRequestController::class, 'cancel']);

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
