<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\DoctorQueue;
use App\Models\Queues\TriageQueue;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TriageQueueController extends QueueBaseController
{
    public function __construct(TriageQueue $model)
    {
        parent::__construct($model);
    }

    public function sendToDoctor(string $session_id) {        
        $data['session_id'] = $session_id;
        $data['created_by'] = Auth::id();

        $createdItem = DoctorQueue::create($data);

        if($createdItem){
            $triage = TriageQueue::where('session_id', $session_id)->first();
            $triage->delete();

            return response(['message' => 'Patient is now in doctor queue'], Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
