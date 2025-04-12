<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\InvoiceAddition;
use App\Models\Patient\PatientDentalService;
use App\Models\Patient\PatientDrug;
use App\Models\Patient\PatientNonPharmaceutical;
use App\Models\Patient\PatientNursing;
use App\Models\Patient\PatientSession;
use App\Models\Patient\PatientTest;
use App\Models\Patient\WardRound;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');
        $hospitalId = Auth::user()->hospital_id;
        $patientSession = PatientSession::where('hospital_id', $hospitalId)->first($sessionId);
        
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

            //DENTAL FEES
            $dental = PatientDentalService::with('dental_service')->where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
            foreach($dental as $item) {
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
            $lab = PatientTest::where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
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
                'dental' => $dental,
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

    public function editInvoice(Request $request) {
        $request->validate([
            'session_id' => 'required',
            'source' => 'required',
            'amount' => 'required'
        ]);
        $data = $request->all();

        if($data['source'] == 'Reception Consultation') {
            $session = PatientSession::where('id', $data['session_id'])->where('status', 'ACTIVE')->first();
            $session->consultation_fee = $data['amount'];
            $this->sendResponse($session->save());
        }

        if($data['source'] == 'Reception Registration') {
            $session = PatientSession::where('id', $data['session_id'])->where('status', 'ACTIVE')->first();
            $session->registration_fee = $data['amount'];
            $this->sendResponse($session->save());
        }

        if($data['source'] == 'Bed') {
            $wardRounds = WardRound::where('session_id', $data['session_id'])->where('bed_price', $data['previous_amount'])->get();
            foreach($wardRounds as $round) {
                $round->bed_price = $data['amount'];
                $round->save();
            }
            $this->sendResponse(true);
        }

        if($data['source'] == 'Doctor Inpatient') {
            $wardRounds = WardRound::where('session_id', $data['session_id'])->where('doctor_price', $data['previous_amount'])->get();
            foreach($wardRounds as $round) {
                $round->doctor_price = $data['amount'];
                $round->save();
            }
            $this->sendResponse(true);
        }

        if($data['source'] == 'Nurse Inpatient') {
            $wardRounds = WardRound::where('session_id', $data['session_id'])->where('nurse_price', $data['previous_amount'])->get();
            foreach($wardRounds as $round) {
                $round->nurse_price = $data['amount'];
                $round->save();
            }
            $this->sendResponse(true);
        }

        if($data['source'] == 'Lab') {
            $test = PatientTest::where('id', $data['item_id'])->where('status', 'ACTIVE')->first();
            $test->price = $data['amount'];
            $this->sendResponse($test->save());
        }

        if($data['source'] == 'Dental') {
            $service = PatientDentalService::where('id', $data['item_id'])->where('status', 'ACTIVE')->first();
            $service->price = $data['amount'];
            $this->sendResponse($service->save());
        }

        if($data['source'] == 'Pharmaceuticals') {
            $drug = PatientDrug::where('id', $data['item_id'])->where('status', 'ACTIVE')->first();
            $drug->unit_price = $data['amount'];
            $this->sendResponse($drug->save());
        }
        
        if($data['source'] == 'Non-Pharmaceuticals') {
            $nonPharm = PatientNonPharmaceutical::where('id', $data['item_id'])->where('status', 'ACTIVE')->first();
            $nonPharm->unit_price = $data['amount'];
            $this->sendResponse($nonPharm->save());
        }

        if($data['source'] == 'Nurse') {
            $nurse = PatientNursing::where('id', $data['item_id'])->where('status', 'ACTIVE')->first();
            $nurse->price = $data['amount'];
            $this->sendResponse($nurse->save());
        }
    }

    private function sendResponse($success) {
        if($success){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
