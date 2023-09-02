<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\PaymentRequest;
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
        
        return PaymentRequest::where('status', 'NOT_PAID')->paginate($pageSize, ['*'], 'page', $pageIndex);
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
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $request = PaymentRequest::find($id);
        $updatedRequest = $request->update($data);

        if($updatedRequest){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cancel request.
     */
    public function cancel(string $id)
    {
        $request = PaymentRequest::find($id);
        $request->status = 'CANCELLED';

        if($request->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
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
        $request = PaymentRequest::find($id);
        $request->status = 'DELETED';

        if($request->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
