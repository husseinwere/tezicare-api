<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientConditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patientId = $request->query('patient_id');

        $condition = PatientCondition::where('patient_id', $patientId)->get();
        
        return $condition;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required',
            'condition' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        return PatientCondition::create($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'condition' => 'required'
        ]);
        $data = $request->all();

        $condition = PatientCondition::find($id);
        $condition->update($data);

        return $condition;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return PatientCondition::destroy($id);
    }
}
