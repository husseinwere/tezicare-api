<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentRequest;
use App\Models\Queues\AdmissionQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PaymentRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $hospitalId = Auth::user()->hospital_id;
        
        return PaymentRequest::with(['session.patient', 'created_by'])->where('hospital_id', $hospitalId)->where('status', 'NOT_PAID')
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
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $createdRequest = PaymentRequest::create($data);

        if($createdRequest){
            if($data['source'] == 'Admission deposit') {
                $queue = AdmissionQueue::where('session_id', $data['session_id'])->first();
                $queue->status = 'PENDING_PAYMENT';
                $queue->save();
            }

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
