<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\NurseInstruction;
use App\Models\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NurseInstructionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        return NurseInstruction::with('created_by')->where('session_id', $sessionId)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'instruction' => 'required'
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $records = explode(';', $data['instruction']);
        try {
            foreach ($records as $record) {
                $data['instruction'] = $record;
                NurseInstruction::create($data);
            }
            return response(null, Response::HTTP_CREATED);
        }
        catch (\Exception $e) {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $instruction = NurseInstruction::find($id);

        $updatedInstruction = $instruction->update($data);

        if($updatedInstruction){
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
        $instruction = NurseInstruction::find($id);
        $session_id = $instruction['session_id'];
        $session = PatientSession::where('id', $session_id)->where('status', 'ACTIVE')->first();

        if($session) {
            return NurseInstruction::destroy($id);
        }
        else {
            return response(['message' => 'You cannot edit records of a discharged patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
