<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Nurse\NursingService;
use App\Models\Patient\PatientNursing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientNursingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        return PatientNursing::with(['nursing_service', 'created_by'])->where('session_id', $sessionId)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'service_id' => 'required'
        ]);

        $data = $request->all();

        $service = NursingService::find($data['service_id']);

        $data['service'] = $service['service'];
        $data['price'] = $service['price'];
        $data['created_by'] = Auth::id();

        $createdService = PatientNursing::create($data);

        if($createdService){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $service = PatientNursing::find($id);

        $updatedService = $service->update($data);

        if($updatedService){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $service = PatientNursing::find($id);

        if($service['payment_status'] == "PAID" || $service['status'] == "CLEARED") {
            return response(['message' => 'You cannot delete paid for or cleared services.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            if(PatientNursing::destroy($id)) {
                return response(null, Response::HTTP_NO_CONTENT);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
