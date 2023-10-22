<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientRecommendation;
use App\Models\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientRecommendationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $recommendations = PatientRecommendation::where('session_id', $sessionId)->get();
        
        return $recommendations;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'recommendation' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $records = explode(';', $data['recommendation']);
        for($i=0; $i<count($records); $i++) {
            $data['recommendation'] = $records[$i];

            PatientRecommendation::create($data);
        }

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'recommendation' => 'required'
        ]);
        $data = $request->all();

        $recommendation = PatientRecommendation::find($id);
        $recommendation->update($data);

        return response(null, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $recommendation = PatientRecommendation::find($id);
        $session_id = $recommendation['session_id'];
        $session = PatientSession::where('id', $session_id)->where('status', 'ACTIVE')->first();

        if($session) {
            return PatientRecommendation::destroy($id);
        }
        else {
            return response(['message' => 'You cannot edit records of a discharged patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
