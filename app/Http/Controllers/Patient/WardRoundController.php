<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientSession;
use App\Models\Patient\WardRound;
use App\Models\Queues\InpatientQueue;
use App\Models\Ward\Bed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WardRoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $sessionId = $request->query('session_id');

        return WardRound::with(['session.patient', 'session.consultation', 'bed.ward', 'records.created_by'])
                        ->where('session_id', $sessionId)->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'bed_id' => 'required',
            'bed_price' => 'required',
            'doctor_price' => 'required',
            'nurse_price' => 'required',
            'created_at' => 'required'
        ]);
        $data = $request->all();

        //check if round with the same date already exists
        $existingRound = WardRound::where('session_id', $data['session_id'])->whereDate('created_at', $data['created_at'])->exists();
        
        if($existingRound){
            return response(['message' => 'Ward round for this date already exists'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            $createdRound = WardRound::create($data);

            if($createdRound){
                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $round = WardRound::find($id);
        $updatedRound = $round->update($data);

        if($updatedRound){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(string $id)
    {
        if(WardRound::destroy($id)) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function transferPatient(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'bed_id' => 'required'
        ]);
        $data = $request->all();

        $inpatient = InpatientQueue::where('session_id', $data['session_id']);
        $inpatient->bed_id = $data['bed_id'];

        if($inpatient->save()){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
