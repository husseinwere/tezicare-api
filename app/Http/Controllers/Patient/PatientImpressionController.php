<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientImpression;
use App\Models\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class PatientImpressionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $impressions = PatientImpression::where('session_id', $sessionId)->get();
        
        return $impressions;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'impression' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $records = explode(';', $data['impression']);
        for($i=0; $i<count($records); $i++) {
            $data['impression'] = $records[$i];

            PatientImpression::create($data);
        }

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'impression' => 'required'
        ]);
        $data = $request->all();

        $impression = PatientImpression::find($id);
        $impression->update($data);

        return response(null, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $impression = PatientImpression::find($id);
        $session_id = $impression['session_id'];
        $session = PatientSession::where('session_id', $session_id)->where('status', 'ACTIVE')->first();

        if($session) {
            return PatientImpression::destroy($id);
        }
        else {
            return response(['message' => 'You cannot edit records of a discharged patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
