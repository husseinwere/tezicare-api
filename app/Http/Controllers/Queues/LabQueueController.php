<?php

namespace App\Http\Controllers\Queues;

use App\Models\Patient\PatientTest;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\LabQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LabQueueController extends QueueBaseController
{
    public function __construct(LabQueue $model)
    {
        parent::__construct($model);
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required'
        ]);
        
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $queuePresent = LabQueue::where('session_id', $data['session_id'])->first();
        if(!$queuePresent) {
            $generalTests = PatientTest::with('lab_test')
                                        ->where('session_id', $data['session_id'])
                                        ->whereHas('lab_test', function ($query) {
                                            $query->where('lab', 'General');
                                        })
                                        ->get();

            if(!$generalTests->isEmpty()) {
                $generalQueue = LabQueue::create($data);
            }
            else {
                return response(['message' => 'Please add a lab test before sending patient to lab.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if($generalQueue){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'Patient is already in lab queue.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function completeSession(string $sessionId) {
        $queue = LabQueue::where('session_id', $sessionId)->first();
        $doctorQueue = DoctorQueue::where('session_id', $sessionId)->first();

        if(LabQueue::destroy($queue->id)) {
            $doctorQueue->status = 'FROM_LAB';
            $doctorQueue->save();

            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
