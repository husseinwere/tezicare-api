<?php

namespace App\Http\Controllers\Queues;

use App\Models\Patient\PatientSession;
use App\Models\Queues\ClearanceQueue;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\InpatientQueue;
use App\Models\Queues\LabQueue;
use App\Models\Queues\NurseQueue;
use App\Models\Queues\PharmacyQueue;
use App\Models\Queues\RadiologyQueue;
use App\Models\Queues\TriageQueue;
use App\Models\Ward\Bed;
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
        $activeQueues = [];
        
        if(TriageQueue::where('session_id', $sessionId)->exists()) $activeQueues[] = 'triage';
        if(PharmacyQueue::where('session_id', $sessionId)->exists()) $activeQueues[] = 'pharmacy';
        if(NurseQueue::where('session_id', $sessionId)->exists()) $activeQueues[] = 'nurse';
        if(LabQueue::where('session_id', $sessionId)->exists()) $activeQueues[] = 'lab';
        if(RadiologyQueue::where('session_id', $sessionId)->exists()) $activeQueues[] = 'radiology';

        if(count($activeQueues) > 0) {
            return response(['message' => 'Please complete the patient sessions in the following stations before discharging: '. implode(', ', $activeQueues)], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            $doctorQueue = DoctorQueue::where('session_id', $sessionId)->first();
            if($doctorQueue) {
                $doctorQueue->status = 'CLEARANCE';
                $doctorQueue->save();
            }

            $inpatientsQueue = InpatientQueue::where('session_id', $sessionId)->first();
            if($inpatientsQueue) {
                $inpatientsQueue->status = 'CLEARANCE';
                $inpatientsQueue->save();

                $bed = Bed::find($inpatientsQueue->bed_id);
                $bed->status = 'UNOCCUPIED';
                $bed->save();
            }

            $createdItem = ClearanceQueue::create([
                'hospital_id' => Auth::user()->hospital_id,
                'session_id' => $sessionId,
                'created_by' => Auth::id()
            ]);

            $session = PatientSession::find($sessionId);
            $session->discharged = Carbon::now();
            $session->save();

            if($createdItem){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
