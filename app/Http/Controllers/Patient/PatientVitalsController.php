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
        return PatientVitals::with(['session', 'created_by'])
                            ->whereHas('session', function ($query) use ($patient_id) {
                                $query->where('patient_id', $patient_id);
                            })
                            ->latest()->first();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdVitals = PatientVitals::create($data);

        if($createdVitals){
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

        $vitals = PatientVitals::find($id);
        $updatedVitals = $vitals->update($data);

        if($updatedVitals){
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
        if(PatientVitals::destroy($id)) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
