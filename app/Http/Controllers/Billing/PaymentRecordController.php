<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentRecord;
use App\Models\Billing\PaymentRequest;
use App\Models\Patient\PatientDrug;
use App\Models\Patient\PatientNonPharmaceutical;
use App\Models\Patient\PatientNursing;
use App\Models\Patient\PatientTest;
use App\Models\Queues\AdmissionQueue;
use App\Models\Queues\TriageQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PaymentRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        
        return PaymentRecord::paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Get records for a specific patient session.
     */
    public function sessionRecords(string $id)
    {   
        $records = PaymentRecord::with(['request', 'created_by'])->where('session_id', $id)->where('status', '<>', 'DELETED')->get();
        $totalAmountPaid = $records->sum('amount');

        return [
            'paymentRecords' => $records,
            'totalAmountPaid' => $totalAmountPaid
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'request_id' => 'required',
            'payments' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $payments = $data['payments'];
        try {
            foreach ($payments as $payment) {
                $data['payment_method'] = $payment['payment_method'];
                $data['mpesa_code'] = $payment['mpesa_code'];
                $data['insurance_id'] = $payment['insurance_id'];
                $data['amount'] = $payment['amount'];

                if($data['insurance_id']) {
                    $data['status'] = 'UNCLAIMED';
                }
                else {
                    $data['status'] = 'ACTIVE';
                }

                PaymentRecord::create($data);
            }

            //MARK REQUEST ITEMS AS PAID
            $this->markAsPaid($data);

            //MARK PAYMENT REQUEST AS PAID
            $paymentRequest = PaymentRequest::find($data['request_id']);
            $paymentRequest->status = 'PAID';
            $paymentRequest->save();

            return response(null, Response::HTTP_CREATED);
        }
        catch (\Exception $e) {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $paymentRecord = PaymentRecord::find($id);
        $updatedRecord = $paymentRecord->update($data);

        if($updatedRecord){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentRecord = PaymentRecord::find($id);
        $paymentRecord->status = 'DELETED';

        if($paymentRecord->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function markAsPaid($data) {
        $request = PaymentRequest::find($data['request_id']);
        $items = $request['items'];

        if($request['source'] == 'Reception consultation') {
            $queue = TriageQueue::where('session_id', $request['session_id'])->first();
            $queue->status = 'ACTIVE';
            $queue->save();
        }
        else if($request['source'] == 'Admission deposit') {
            $queue = AdmissionQueue::where('session_id', $request['session_id'])->first();
            $queue->status = 'PAID';
            $queue->save();
        }

        if($items) {
            $items = explode(',', $items);

            foreach($items as $itemId) {
                if($request['source'] == 'Lab' || $request['source'] == 'Radiology') {
                    $test = PatientTest::find($itemId);
                    $test->payment_status = 'PAID';
                    $test->save();
                }
                else if($request['source'] == 'Non-Pharmaceuticals') {
                    $nonPharmaceutical = PatientNonPharmaceutical::find($itemId);
                    $nonPharmaceutical->payment_status = 'PAID';
                    $nonPharmaceutical->save();
                }
                else if($request['source'] == 'Nurse') {
                    $service = PatientNursing::find($itemId);
                    $service->payment_status = 'PAID';
                    $service->save();
                }
                else if($request['source'] == 'Pharmacy') {
                    $drug = PatientDrug::find($itemId);
                    $drug->payment_status = 'PAID';
                    $drug->save();
                }
            }
        }
    }
}
