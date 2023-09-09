<?php

namespace App\Http\Controllers;

use App\Models\PatientSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientSessionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required'
        ]);
        $data = $request->all();

        $existingRecord = PatientSession::where('patient_id', $data['patient_id'])->where('status', 'ACTIVE')->first();

        if ($existingRecord) {
            return response(['message' => 'A session has already been started with this patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data['created_by'] = Auth::id();

        return PatientSession::create($data);
    }

    public function show(string $id)
    {
        return PatientSession::find($id);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $session = PatientSession::find($id);
        $session->update($data);

        return $session;
    }

    public function discharge(string $id)
    {
        //DELETE PATIENT FROM QUEUES (INCASE iTS NECESSARY)

        //UPDATE DISCHARGE DATE TIME
        $currentDateTime = Carbon::now();

        $session = PatientSession::find($id);
        $session->discharge = $currentDateTime;
        $session->save();

        //SEND TO CLEARANCE QUEUE AT CASHIER

        return $session;
    }

    public function destroy(string $id)
    {
        $session = PatientSession::find($id);
        $session->status = 'DELETED';
        $session->save();

        return $session;
    }
}
