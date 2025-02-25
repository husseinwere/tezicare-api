<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\InvoiceAddition;
use App\Models\Patient\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InvoiceAdditionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'category' => 'required',
            'name' => 'required',
            'quantity' => 'required',
            'rate' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $validSession = PatientSession::where('id', $data['session_id'])->where('status', 'ACTIVE')->first();
        if(!$validSession) {
            return response(['message' => 'Patient session not found.'], Response::HTTP_BAD_REQUEST);
        }

        $createdAddition = InvoiceAddition::create($data);

        if($createdAddition){
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

        $addition = InvoiceAddition::find($id);
        $updatedAddition = $addition->update($data);

        if($updatedAddition){
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
        $invoiceAddition = InvoiceAddition::find($id);
        $invoiceAddition->status = 'DELETED';

        if($invoiceAddition->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
