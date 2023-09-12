<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientVitals;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientVitalsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $vitals = PatientVitals::where('session_id', $sessionId)->get();
        
        return $vitals;
    }

    /**
     * Display the session latest vitals.
     */
    public function getLatestVitals($session_id)
    {
        return PatientVitals::where('session_id', $session_id)->latest()->first();
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
