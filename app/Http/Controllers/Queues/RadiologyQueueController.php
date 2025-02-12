<?php

namespace App\Http\Controllers\Queues;

use App\Models\Patient\PatientTest;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\RadiologyQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RadiologyQueueController extends QueueBaseController
{
    public function __construct(RadiologyQueue $model)
    {
        parent::__construct($model);
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required'
        ]);
        
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $queuePresent = RadiologyQueue::where('session_id', $data['session_id'])->first();
        if(!$queuePresent) {
            $radiologyTests = PatientTest::with('lab_test')
                                        ->where('session_id', $data['session_id'])
                                        ->whereHas('lab_test', function ($query) {
                                            $query->where('lab', 'Radiology');
                                        })
                                        ->get();

            if(!$radiologyTests->isEmpty()) {
                $radiologyQueue = RadiologyQueue::create($data);
            }
            else {
                return response(['message' => 'Please add a radiology test before sending patient to radiology lab.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if($radiologyQueue){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'Patient is already in radiology queue.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function completeSession(string $sessionId) {
        $queue = RadiologyQueue::where('session_id', $sessionId)->first();
        $doctorQueue = DoctorQueue::where('session_id', $sessionId)->first();

        if(RadiologyQueue::destroy($queue->id)) {
            $doctorQueue->status = 'FROM_RADIOLOGY';
            $doctorQueue->save();

            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
