<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientInsurance;
use Illuminate\Support\Facades\Auth;

class PatientInsuranceController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patient_id = $request->input('patient_id');

        return PatientInsurance::join('insurance_covers', 'patient_insurances.patient_id', '=', 'insurance_covers.id')
                                    ->select('patient_insurances.*', 'insurance_covers.insurance as insurance')
                                    ->where('patient_insurances.patient_id', $patient_id)->where('patient_insurances.status', 'ACTIVE')
                                    ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required',
            'insurance_id' => 'required',
            'card_no' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        return PatientInsurance::create($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'card_no' => 'required'
        ]);
        $data = $request->all();

        $insurance = PatientInsurance::find($id);
        $insurance->update($data);

        return $insurance;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $insurance = PatientInsurance::find($id);
        $insurance->status = 'DELETED';
        $insurance->save();

        return $insurance;
    }
}
