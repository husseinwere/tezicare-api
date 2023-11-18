<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\AdmissionQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AdmissionQueueController extends QueueBaseController
{
    public function __construct(AdmissionQueue $model)
    {
        parent::__construct($model);
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required'
        ]);
        
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $queuePresent = AdmissionQueue::where('session_id', $data['session_id'])->first();
        if(!$queuePresent) {
            $createdQueue = AdmissionQueue::create($data);

            if($createdQueue){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'Patient is already in admission queue.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
