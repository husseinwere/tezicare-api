<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Hospital\ConsultationType;
use App\Models\Patient\PatientSession;
use App\Models\Patient\WardRound;
use App\Models\Queues\InpatientQueue;
use App\Models\Ward\Bed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WardRoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $sessionId = $request->query('session_id');

        return WardRound::with(['session.patient', 'bed.ward', 'doctor', 'nurse'])
                        ->where('session_id', $sessionId)->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function getWardDetails(string $id)
    {
        $currentDate = date('Y-m-d');

        $inpatient = InpatientQueue::with(['session.patient', 'bed.ward'])->where('session_id', $id)->latest()->first();

        $currentRound = WardRound::where('session_id', $id)->where('created_at', 'like', $currentDate . '%')->latest()->first();
        
        return [
            'patient' => $inpatient->session->patient,
            'current_day' => $currentDate,
            'bed' => $inpatient->bed,
            'current_round' => $currentRound
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'bed_id' => 'required'
        ]);
        $data = $request->all();

        $bed = Bed::find($data['bed_id']);
        $data['bed_price'] = $bed->ward->price;

        $session = PatientSession::find($data['session_id']);
        $consultation_type = ConsultationType::where('name', $session->consultation_type)->first();
        $data['doctor_price'] = $consultation_type->inpatient_doctor_rate;
        $data['nurse_price'] = $consultation_type->inpatient_nurse_rate;

        $createdRound = WardRound::create($data);

        if($createdRound){
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

        $round = WardRound::find($id);
        $updatedRound = $round->update($data);

        if($updatedRound){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
