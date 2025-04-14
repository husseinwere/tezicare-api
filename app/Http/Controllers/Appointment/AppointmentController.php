<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $patient_id = $request->query('patient_id');
        $hospital_id = Auth::user()->hospital_id;

        $query = Appointment::with(['patient', 'created_by'])->where('hospital_id', $hospital_id)->where('status', 'ACTIVE');

        if($patient_id) {
            $query->where('patient_id', $patient_id);
        }

        return $query->orderBy('appointment_date', 'desc')->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required',
            'appointment_date' => 'required',
            'duration' => 'required',
            'description' => 'required'
        ]);
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $createdAppointment = Appointment::create($data);

        if($createdAppointment){
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

        $appointment = Appointment::find($id);
        $updatedAppointment = $appointment->update($data);

        if($updatedAppointment){
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
        $appointment = Appointment::find($id);
        $appointment->status = 'DELETED';

        if($appointment->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
