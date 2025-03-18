<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientSession;
use App\Models\Patient\PatientVisit;
use App\Models\Queues\DoctorQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientVisitController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required'
        ]);
        $data = $request->all();

        $existingRecord = PatientVisit::where('session_id', $data['session_id'])->where('status', 'ACTIVE')->first();

        if ($existingRecord) {
            return response(['message' => 'A visit has already been started in this session.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data['created_by'] = Auth::id();

        $createdVisit = PatientVisit::create($data);

        if($createdVisit){
            DoctorQueue::create($data);

            $session = PatientSession::find($data['session_id']);
            $session->status = 'ACTIVE';
            $session->save();

            return response($createdVisit, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
