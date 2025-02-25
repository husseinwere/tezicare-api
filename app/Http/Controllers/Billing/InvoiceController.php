<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\InvoiceAddition;
use App\Models\Patient\PatientDrug;
use App\Models\Patient\PatientNonPharmaceutical;
use App\Models\Patient\PatientNursing;
use App\Models\Patient\PatientSession;
use App\Models\Patient\PatientTest;
use App\Models\Patient\WardRound;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');
        $patientSession = PatientSession::find($sessionId);
        
        if($patientSession) {
            $totalInvoiceAmount = 0;

            $consultation_fee = $patientSession->consultation_fee;
            $registration_fee = $patientSession->registration_fee;

            $totalInvoiceAmount += $consultation_fee;
            $totalInvoiceAmount += $registration_fee;

            $consultation = [
                'name' => 'Consultation fee',
                'amount' => $consultation_fee,
                'created_at' => $patientSession->created_at
            ];
            $registration = [
                'name' => 'Registration fee',
                'amount' => $registration_fee,
                'created_at' => $patientSession->created_at
            ];

            $reception = [
                'consultation' => $consultation,
                'registration' => $registration
            ];

            //NURSE FEES
            $nurse = PatientNursing::where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
            foreach($nurse as $item) {
                $totalInvoiceAmount += $item->price;
            }

            $bedRecords = array();
            $doctorRecords = array();
            $nurseRecords = array();
            
            if($patientSession->patient_type == 'INPATIENT') {
                //BED FEES
                $bedRecords = WardRound::with('bed.ward')
                                        ->select('bed_id', 'bed_price', DB::raw('COUNT(*) as quantity'), DB::raw('SUM(bed_price) as total'))
                                        ->where('session_id', $sessionId)
                                        ->groupBy('bed_id', 'bed_price')
                                        ->get();
                foreach($bedRecords as $item) {
                    $totalInvoiceAmount += $item->total;
                }

                //DOCTOR ROUND FEES
                $doctorRecords = WardRound::select('doctor_price', DB::raw('COUNT(*) as quantity'), DB::raw('SUM(doctor_price) as total'))
                                        ->where('session_id', $sessionId)
                                        ->groupBy('doctor_price')
                                        ->get();
                foreach($doctorRecords as $item) {
                    $totalInvoiceAmount += $item->total;
                }

                //NURSE ROUND FEES
                $nurseRecords = WardRound::select('nurse_price', DB::raw('COUNT(*) as quantity'), DB::raw('SUM(nurse_price) as total'))
                                        ->where('session_id', $sessionId)
                                        ->groupBy('nurse_price')
                                        ->get();
                foreach($nurseRecords as $item) {
                    $totalInvoiceAmount += $item->total;
                }
            }

            //NON-PHARMACEUTICALS
            $nonPharmaceuticals = PatientNonPharmaceutical::with('non_pharmaceutical')->where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
            foreach($nonPharmaceuticals as $item) {
                $totalPrice = $item->quantity * $item->unit_price;
                $totalInvoiceAmount += $totalPrice;
            }

            //LAB FEES
            $lab = PatientTest::join('lab_results', 'patient_tests.id', '=', 'lab_results.patient_test_id')
                                ->where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
            foreach($lab as $item) {
                $totalInvoiceAmount += $item->price;
            }

            //PHARMACY FEES
            $pharmacy = PatientDrug::with('pharmaceutical')->where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
            foreach($pharmacy as $item) {
                $totalPrice = $item->quantity * $item->unit_price;
                $totalInvoiceAmount += $totalPrice;
            }

            //INVOICE ADDITIONS
            $additions = InvoiceAddition::where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
            foreach($additions as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;
            }

            return [
                'invoice_total' => $totalInvoiceAmount,
                'reception' => $reception,
                'nurse' => $nurse,
                'nonPharmaceuticals' => $nonPharmaceuticals,
                'lab' => $lab,
                'pharmacy' => $pharmacy,
                'bed' => $bedRecords,
                'doctor_rounds' => $doctorRecords,
                'nurse_rounds' => $nurseRecords,
                'invoice_additions' => $additions
            ];
        }
        else {
            return response(['message' => 'Patient session not found.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
