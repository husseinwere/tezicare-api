<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentRecord;
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
    public function sessionRecords(Request $request, string $id)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        
        return PaymentRecord::where('session_id', $id)->where('status', 'ACTIVE')->paginate($pageSize, ['*'], 'page', $pageIndex);
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

        $payments = json_decode($data['payments']);
        try {
            foreach ($payments as $payment) {
                $data['payment_method'] = $payment['payment_method'];
                $data['mpesa_code'] = $payment['mpesa_code'];
                $data['insurance_id'] = $payment['insurance_id'];
                $data['amount'] = $payment['amount'];

                PaymentRecord::create($data);
            }
            return response(null, Response::HTTP_CREATED);
        }
        catch (\Exception $e) {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
