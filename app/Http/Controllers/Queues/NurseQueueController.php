<?php

namespace App\Http\Controllers\Queues;

use App\Models\Patient\NurseInstruction;
use App\Models\Patient\PatientSession;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\NurseQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NurseQueueController extends QueueBaseController
{
    public function __construct(NurseQueue $model)
    {
        parent::__construct($model);
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required'
        ]);
        
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $queuePresent = NurseQueue::where('session_id', $data['session_id'])->first();
        if(!$queuePresent) {
            $session = PatientSession::find($data['session_id']);
            $instructions = NurseInstruction::where('session_id', $data['session_id'])->get();

            if(!$instructions->isEmpty() || $session->patient_type == 'DIRECT_SERVICE') {
                $nurseQueue = NurseQueue::create($data);
            }
            else {
                return response(['message' => 'Please add a nurse instruction before sending patient to nurse.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if($nurseQueue){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'Patient is already in nurse queue.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function completeSession(string $sessionId) {
        $queue = NurseQueue::where('session_id', $sessionId)->first();
        $doctorQueue = DoctorQueue::where('session_id', $sessionId)->first();

        if(NurseQueue::destroy($queue->id)) {
            $doctorQueue->status = 'FROM_NURSE';
            $doctorQueue->save();

            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
