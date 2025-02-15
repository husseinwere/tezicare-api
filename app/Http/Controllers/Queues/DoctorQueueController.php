<?php

namespace App\Http\Controllers\Queues;

use App\Models\Patient\PatientSession;
use App\Models\Queues\ClearanceQueue;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\InpatientQueue;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DoctorQueueController extends QueueBaseController
{
    public function __construct(DoctorQueue $model)
    {
        parent::__construct($model);
    }

    public function completeSession(string $sessionId) {
        $doctorQueue = DoctorQueue::where('session_id', $sessionId)->first();
        if($doctorQueue) {
            $doctorQueue->status = 'CLEARANCE';
            $doctorQueue->save();
        }

        $inpatientsQueue = InpatientQueue::where('session_id', $sessionId)->first();
        if($inpatientsQueue) {
            $inpatientsQueue->status = 'CLEARANCE';
            $inpatientsQueue->save();
        }

        $createdItem = ClearanceQueue::create([
            'session_id' => $sessionId,
            'created_by' => Auth::id()
        ]);

        //UPDATE DISCHARGE DATE TIME
        $currentDateTime = Carbon::now();

        $session = PatientSession::find($sessionId);
        $session->discharged = $currentDateTime;
        $session->save();

        if($createdItem){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
