<?php

namespace App\Http\Controllers\Appointment;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Hospital\Hospital;
use App\Models\Patient\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            $response = $this->sendSmsNotification($createdAppointment);

            if ($response && $response->successful()) {
                return response(['message' => 'Appointment scheduled successfully and user notified via SMS.'], Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'Appointment scheduled successfully but user notification failed.'], Response::HTTP_CREATED);
            }
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
            $response = $this->sendSmsNotification($updatedAppointment);

            if ($response && $response->successful()) {
                return response(['message' => 'Appointment updated successfully and user notified via SMS.'], Response::HTTP_OK);
            }
            else {
                return response(['message' => 'Appointment updated successfully but user notification failed.'], Response::HTTP_OK);
            }
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

    private function formatPhoneNumber($phoneNumber)
    {
        // Remove any non-digit characters
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Check if the phone number starts with '0' and replace it with '254'
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '254' . substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }

    private function sendSmsNotification(Appointment $appointment)
    {
        $patient = Patient::find($appointment->patient_id);
        $hospital = Hospital::find($appointment->hospital_id);
        $appointment_date = date('l, F j, Y', strtotime($appointment->appointment_date));
        $patientPhone = $this->formatPhoneNumber($patient->phone);
        $message = "Hi {$patient->first_name},\n\nYour appointment has been scheduled at {$hospital->name} on {$appointment_date} for {$appointment->duration} minutes. We are looking forward to seeing you! To reschedule, please contact us at {$hospital->phone}.\n\nThank you for choosing us.";

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'apikey' => env('BULKSMS_APIKEY'),
                'cache-control' => 'no-cache',
                'content-type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('http://bulksms.vsoft.co.ke/SMSApi/send', [
                'userid' => env('BULKSMS_USERID'),
                'password' => env('BULKSMS_PASSWORD'),
                'senderid' => env('BULKSMS_SENDERID'),
                'msgType' => 'text',
                'duplicatecheck' => 'true',
                'sendMethod' => 'quick',
                'mobile' => $patientPhone,
                'msg' => $message,
                'output' => 'json'
            ]);

            Log::error('BulkSMS API call success', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                // Handle non-successful responses
                Log::error('BulkSMS API call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }
        catch (\Exception $e) {
            $response = null;
            Log::error('Exception during BulkSMS API call', [
                'message' => $e->getMessage(),
            ]);
        }

        return $response;
    }
}
