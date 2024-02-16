<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentRequest;
use App\Models\Patient\PatientDrug;
use App\Models\Patient\PatientNonPharmaceutical;
use App\Models\Patient\PatientNursing;
use App\Models\Patient\PatientTest;
use App\Models\Patient\WardRound;
use App\Models\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

            $ward_rounds = array();
            if($patientSession->patient_type == 'INPATIENT') {
                //BED FEES
                $ward_rounds = WardRound::with('ward')->where('session_id', $sessionId)->get();
                foreach($ward_rounds as $item) {
                    $totalInvoiceAmount += $item->bed_price;

                    if($item->doctor_price) {
                        $totalInvoiceAmount += $item->doctor_price;
                    }

                    if($item->nurse_price) {
                        $totalInvoiceAmount += $item->nurse_price;
                    }
                }
            }

            //NON-PHARMACEUTICALS
            $nonPharmaceuticals = PatientNonPharmaceutical::with('nonPharmaceutical')->where('session_id', $sessionId)->where('status', 'ACTIVE')->get();
            foreach($nonPharmaceuticals as $item) {
                $totalPrice = $item->quantity * $item->unit_price;
                $totalInvoiceAmount += $totalPrice;
            }

            //LAB FEES
            $lab = PatientTest::join('lab_results', 'patient_tests.id', '=', 'lab_results.test_id')
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

            return [
                'invoice_total' => $totalInvoiceAmount,
                'reception' => $reception,
                'nurse' => $nurse,
                'nonPharmaceuticals' => $nonPharmaceuticals,
                'lab' => $lab,
                'pharmacy' => $pharmacy,
                'ward' => $ward_rounds
            ];
        }
        else {
            return response(['message' => 'Patient session not found.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $paymentRequest = PaymentRequest::find($id);
        $updatedRequest = $paymentRequest->update($data);

        if($updatedRequest){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
