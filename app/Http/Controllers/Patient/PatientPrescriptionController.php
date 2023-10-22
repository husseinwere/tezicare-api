<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientPrescription;
use App\Models\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientPrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $prescriptions = PatientPrescription::where('session_id', $sessionId)->get();
        
        return $prescriptions;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'drug' => 'required',
            'dosage' => 'dosage'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdPrescription = PatientPrescription::create($data);

        if($createdPrescription){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $prescription = PatientPrescription::find($id);
        $updatedPrescription = $prescription->update($data);

        if($updatedPrescription){
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
        $prescription = PatientPrescription::find($id);
        $session_id = $prescription['session_id'];
        $session = PatientSession::where('id', $session_id)->where('status', 'ACTIVE')->first();

        if($session) {
            if(PatientPrescription::destroy($id)) {
                return response(null, Response::HTTP_NO_CONTENT);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'You cannot edit records of a discharged patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
