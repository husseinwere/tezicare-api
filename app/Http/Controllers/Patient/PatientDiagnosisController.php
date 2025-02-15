<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientDiagnosis;
use App\Models\Patient\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientDiagnosisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $diagnosis = PatientDiagnosis::where('session_id', $sessionId)->get();
        
        return $diagnosis;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'diagnosis' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $records = explode(';', $data['diagnosis']);
        for($i=0; $i<count($records); $i++) {
            $data['diagnosis'] = $records[$i];

            PatientDiagnosis::create($data);
        }

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'diagnosis' => 'required'
        ]);
        $data = $request->all();

        $diagnosis = PatientDiagnosis::find($id);
        $diagnosis->update($data);

        return response(null, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $diagnosis = PatientDiagnosis::find($id);
        $session_id = $diagnosis['session_id'];
        $session = PatientSession::where('id', $session_id)->where('status', 'ACTIVE')->first();

        if($session) {
            return PatientDiagnosis::destroy($id);
        }
        else {
            return response(['message' => 'You cannot edit records of a discharged patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
