<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\ClearanceQueue;
use App\Models\Queues\DoctorQueue;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DoctorQueueController extends QueueBaseController
{
    public function __construct(DoctorQueue $model)
    {
        parent::__construct($model);
    }

    public function completeSession(string $sessionId) {
        $queue = DoctorQueue::where('session_id', $sessionId)->first();

        if(DoctorQueue::destroy($queue->id)) {
            $createdItem = ClearanceQueue::create([
                'session_id' => $sessionId,
                'created_by' => Auth::id()
            ]);

            if($createdItem){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
