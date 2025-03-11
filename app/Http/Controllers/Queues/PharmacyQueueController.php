<?php

namespace App\Http\Controllers\Queues;

use App\Models\Patient\PatientPrescription;
use App\Models\Patient\PatientSession;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\PharmacyQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PharmacyQueueController extends QueueBaseController
{
    public function __construct(PharmacyQueue $model)
    {
        parent::__construct($model);
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required'
        ]);
        
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $queuePresent = PharmacyQueue::where('session_id', $data['session_id'])->first();
        if(!$queuePresent) {
            $session = PatientSession::find($data['session_id']);
            $prescription = PatientPrescription::where('session_id', $data['session_id'])->get();

            if(!$prescription->isEmpty() || $session->patient_type == 'DIRECT_SERVICE') {
                $pharmacyQueue = PharmacyQueue::create($data);
            }
            else {
                return response(['message' => 'Please add a prescription before sending patient to pharmacy.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if($pharmacyQueue){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'Patient is already in pharmacy queue.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function completeSession(string $sessionId) {
        $queue = PharmacyQueue::where('session_id', $sessionId)->first();
        $doctorQueue = DoctorQueue::where('session_id', $sessionId)->first();

        if(PharmacyQueue::destroy($queue->id)) {
            $doctorQueue->status = 'FROM_PHARMACY';
            $doctorQueue->save();

            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
