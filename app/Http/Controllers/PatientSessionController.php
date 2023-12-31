<?php

namespace App\Http\Controllers;

use App\Models\PatientSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientSessionController extends Controller
{
    public function index(Request $request)
    {
        $patient_id = $request->input('patient_id');

        return PatientSession::where('patient_id', $patient_id)->limit(10)->latest()->get();
    }

    public function getPatientStats(string $patient_id)
    {
        $outpatientCount = PatientSession::where('patient_id', $patient_id)->where('patient_type', 'OUTPATIENT')->count();
        $inpatientCount = PatientSession::where('patient_id', $patient_id)->where('patient_type', 'INPATIENT')->count();

        return [
            'outpatient' => $outpatientCount,
            'inpatient' => $inpatientCount
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required',
            'consultation_type' => 'required'
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
        return PatientSession::leftJoin('users', 'users.id', '=', 'patient_sessions.doctor_id')
                            ->select('patient_sessions.*', DB::raw('CONCAT(users.first_name, " ", users.last_name) as doctor'))
                            ->where('patient_sessions.id', $id)->first();
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
