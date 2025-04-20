<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\ClinicalSummaryRecord;
use App\Models\Patient\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ClinicalSummaryRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        return ClinicalSummaryRecord::where('session_id', $sessionId)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'summary' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $records = explode(';', $data['summary']);
        for($i=0; $i<count($records); $i++) {
            $data['summary'] = $records[$i];

            ClinicalSummaryRecord::create($data);
        }

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'summary' => 'required'
        ]);
        $data = $request->all();

        $record = ClinicalSummaryRecord::find($id);
        $record->update($data);

        return response(null, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $record = ClinicalSummaryRecord::find($id);
        $session_id = $record['session_id'];
        $session = PatientSession::where('id', $session_id)->where('status', 'ACTIVE')->first();

        if($session) {
            return ClinicalSummaryRecord::destroy($id);
        }
        else {
            return response(['message' => 'You cannot edit records of a discharged patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
