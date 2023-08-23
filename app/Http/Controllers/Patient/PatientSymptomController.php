<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientSymptom;
use App\Models\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientSymptomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $symptoms = PatientSymptom::where('session_id', $sessionId)->get();
        
        return $symptoms;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'symptom' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $records = explode(';', $data['symptom']);
        for($i=0; $i<count($records); $i++) {
            $data['symptom'] = $records[$i];

            PatientSymptom::create($data);
        }

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'symptom' => 'required'
        ]);
        $data = $request->all();

        $symptom = PatientSymptom::find($id);
        $symptom->update($data);

        return response(null, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $symptom = PatientSymptom::find($id);
        $session_id = $symptom['session_id'];
        $session = PatientSession::where('session_id', $session_id)->where('status', 'ACTIVE')->first();

        if($session) {
            return PatientSymptom::destroy($id);
        }
        else {
            return response(['error' => 'You cannot edit records of a discharged patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
