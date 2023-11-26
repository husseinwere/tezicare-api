<?php

namespace App\Http\Controllers\Queues;

use App\Models\PatientSession;
use App\Models\Queues\AdmissionQueue;
use App\Models\Queues\InpatientQueue;
use App\Models\Ward\Bed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InpatientQueueController extends QueueBaseController
{
    public function __construct(InpatientQueue $model)
    {
        parent::__construct($model);
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required',
            'ward_id' => 'required',
            'bed_id' => 'required'
        ]);
        
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $session = PatientSession::find($data['session_id']);
        $data['doctor_id'] = $session->doctor_id;

        $queuePresent = InpatientQueue::where('session_id', $data['session_id'])->first();
        if(!$queuePresent) {
            $createdQueue = InpatientQueue::create($data);

            if($createdQueue) {
                //REMOVE FROM ADMISSION QUEUE
                $admissionQueue = AdmissionQueue::where('session_id', $data['session_id'])->first();
                AdmissionQueue::destroy($admissionQueue->id);

                //CHANGE SESSION TO INPATIENT
                $session->patient_type = 'INPATIENT';
                $session->save();

                //CHANGE BED STATUS TO OCCUPIED
                $bed = Bed::find($data['bed_id']);
                $bed->status = 'OCCUPIED';
                $bed->save();

                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'Patient is already admitted to ward.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
