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
use Carbon\Carbon;
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

        $patient_id = $request->query('opno');
        $startAt = $request->query('startAt');
        $endAt = $request->query('endAt');

        $query = PaymentRecord::with(['request', 'session.patient', 'created_by']);

        // add status check if you allow record deletion in future

        if($patient_id) {
            $query->whereHas('session', function($q) use ($patient_id) {
                $q->where('patient_id', $patient_id);
            });
        }

        if($startAt && $endAt) {
            $startAt = Carbon::createFromFormat('Y-m-d', $startAt)->startOfDay();
            $endAt = Carbon::createFromFormat('Y-m-d', $endAt)->endOfDay();

            $query->whereBetween('created_at', [$startAt, $endAt]);
        }
        
        return $query->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
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

    public function paymentTypeReport(Request $request) {
        $startAt = $request->query('startAt');
        $endAt = $request->query('endAt');

        $query = PaymentRecord::with(['request', 'session.patient', 'created_by']);

        if($startAt && $endAt) {
            $startAt = Carbon::createFromFormat('Y-m-d', $startAt)->startOfDay();
            $endAt = Carbon::createFromFormat('Y-m-d', $endAt)->endOfDay();

            $query->whereBetween('created_at', [$startAt, $endAt]);
        }

        $records = $query->get();
        $paymentTypes = $records->groupBy('payment_method');

        $report = [];
        foreach($paymentTypes as $key => $payments) {
            $totalAmount = $payments->sum('amount');
            $report[] = [
                'payment_method' => $key,
                'total_amount' => $totalAmount
            ];
        }

        return $report;
    }

    public function paymentSourceReport(Request $request) {
        $startAt = $request->query('startAt');
        $endAt = $request->query('endAt');

        $query = PaymentRecord::with(['request', 'session.patient', 'created_by']);

        if($startAt && $endAt) {
            $startAt = Carbon::createFromFormat('Y-m-d', $startAt)->startOfDay();
            $endAt = Carbon::createFromFormat('Y-m-d', $endAt)->endOfDay();

            $query->whereBetween('created_at', [$startAt, $endAt]);
        }

        $records = $query->get();
        $paymentSources = $records->groupBy('request.source');

        $report = [];
        foreach($paymentSources as $key => $payments) {
            $totalAmount = $payments->sum('amount');
            $report[] = [
                'source' => $key,
                'total_amount' => $totalAmount
            ];
        }

        return $report;
    }

    public function insuranceTypeReport(Request $request) {
        $startAt = $request->query('startAt');
        $endAt = $request->query('endAt');

        $query = PaymentRecord::with(['request', 'session.patient', 'created_by', 'insurance'])
            ->whereNotNull('insurance_id');

        if($startAt && $endAt) {
            $startAt = Carbon::createFromFormat('Y-m-d', $startAt)->startOfDay();
            $endAt = Carbon::createFromFormat('Y-m-d', $endAt)->endOfDay();

            $query->whereBetween('created_at', [$startAt, $endAt]);
        }

        $records = $query->get();
        $insurancePayments = $records->groupBy('insurance_id');

        $report = [];
        foreach($insurancePayments as $key => $payments) {
            $totalAmount = $payments->sum('amount');
            $insuranceName = $payments->first()->insurance->insurance;
            $report[] = [
                'insurance' => $insuranceName,
                'total_amount' => $totalAmount
            ];
        }

        return $report;
    }

    public function annualPaymentReport(Request $request) {
        $year = $request->query('year', date('Y'));

        $query = PaymentRecord::with(['request', 'session.patient', 'created_by']);

        $records = $query->whereYear('created_at', $year)->get();
        $months = $records->groupBy(function($record) {
            return Carbon::parse($record->created_at)->format('m');
        });

        $report = [];
        $ms = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        foreach($ms as $m) {
            $report[] = [
                'month' => $m,
                'total_amount' => 0
            ];
        }

        foreach($months as $key => $payments) {
            $totalAmount = $payments->sum('amount');
            $report[$key - 1]['total_amount'] = $totalAmount;
        }

        return $report;
    }
}
