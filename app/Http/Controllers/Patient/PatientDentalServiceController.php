<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Dental\DentalService;
use App\Models\Patient\PatientDentalService;
use App\Models\Patient\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientDentalServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        return PatientDentalService::with(['dental_service', 'created_by'])->where('session_id', $sessionId)->get();
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

        $session = PatientSession::find($data['session_id']);
        $service = DentalService::with('prices')->find($data['service_id']);

        $data['service_name'] = $service['name'];
        $data['price'] = $service['price'];
        $data['created_by'] = Auth::id();

        $prices = $service->prices;
        if($session->insurance_id) {
            $insurancePrice = $prices->where('insurance_id', $session->insurance_id)->first();
            if($insurancePrice) {
                $data['price'] = $insurancePrice['price'];
            }
        }

        $createdService = PatientDentalService::create($data);

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

        $service = PatientDentalService::find($id);

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
        $service = PatientDentalService::find($id);

        if($service['payment_status'] == "PAID" || $service['status'] == "CLEARED") {
            return response(['message' => 'You cannot delete paid for or cleared services.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            if(PatientDentalService::destroy($id)) {
                return response(null, Response::HTTP_NO_CONTENT);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
