<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientVitals;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientVitalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $vitals = PatientVitals::where('session_id', $sessionId)->limit(20)->latest()->get();
        
        return $vitals;
    }

    /**
     * Display the session latest vitals.
     */
    public function getLatestVitals(string $patient_id)
    {
        return DB::table('patient_vitals')
                    ->join('patient_sessions', 'patient_vitals.session_id', '=', 'patient_sessions.id')
                    ->join('patients', 'patient_sessions.patient_id', '=', 'patients.id')
                    ->where('patient_sessions.patient_id', $patient_id)
                    ->select('patient_vitals.height', 'patient_vitals.weight', 'patient_vitals.pulse_rate',
                                'patient_vitals.temperature', 'patient_vitals.blood_pressure', 'patient_vitals.spo2')
                    ->orderBy('patient_vitals.created_at', 'desc')->first();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //DELETE FROM PREVIOUS QUEUE WHERE NECESSARY

        //SAVE
        $request->validate([
            'session_id' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        return PatientVitals::create($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $queue = PatientVitals::find($id);
        $queue->update($data);

        return $queue;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return PatientVitals::destroy($id);
    }
}
