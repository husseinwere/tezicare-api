<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Pharmaceutical;
use App\Models\Patient\PatientDrug;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientDrugController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $drugs = PatientDrug::where('session_id', $sessionId)->get();

        return $drugs;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'drug_id' => 'required',
            'dosage' => 'required',
            'quantity' => 'required'
        ]);

        $data = $request->all();

        $drug = Pharmaceutical::find($data['drug_id']);

        $data['unit_price'] = $drug['price'];
        $data['created_by'] = Auth::id();

        $createdDrug = PatientDrug::create($data);

        if($createdDrug){
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

        $drug = PatientDrug::find($id);

        $updatedDrug = $drug->update($data);

        if($updatedDrug){
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
        $drug = PatientDrug::find($id);

        if($drug['payment_status'] == "PAID" || $drug['status'] == "CLEARED") {
            return response(['error' => 'You cannot delete paid for or cleared drugs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            if(PatientDrug::destroy($id)) {
                return response(null, Response::HTTP_NO_CONTENT);
            }
            else {
                return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
