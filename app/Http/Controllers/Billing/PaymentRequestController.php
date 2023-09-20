<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        
        return DB::table('payment_requests')
                ->join('patient_sessions', 'payment_requests.session_id', '=', 'patient_sessions.id')
                ->join('patients', 'patient_sessions.patient_id', '=', 'patients.id')
                ->join('users', 'payment_requests.created_by', '=', 'users.id')
                ->where('payment_requests.status', 'NOT_PAID')
                ->select('payment_requests.id', 'payment_requests.source', 'payment_requests.amount', 'payment_requests.created_at', 
                            DB::raw('CONCAT(users.first_name, " ", users.last_name) as created_by'), 
                            DB::raw('CONCAT(patients.first_name, " ", patients.last_name) as patient_name'), 'patients.id as opno')
                ->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'source' => 'required',
            'amount' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdRequest = PaymentRequest::create($data);

        if($createdRequest){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
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

    /**
     * Cancel request.
     */
    public function cancel(string $id)
    {
        $paymentRequest = PaymentRequest::find($id);
        $paymentRequest->status = 'CANCELLED';

        if($paymentRequest->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
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
        $paymentRequest = PaymentRequest::find($id);
        $paymentRequest->status = 'DELETED';

        if($paymentRequest->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
