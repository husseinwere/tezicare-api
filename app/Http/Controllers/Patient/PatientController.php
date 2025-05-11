<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Hospital\ConsultationType;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientSession;
use App\Models\Patient\PatientVisit;
use App\Models\Queues\LabQueue;
use App\Models\Queues\NurseQueue;
use App\Models\Queues\PharmacyQueue;
use App\Models\Queues\RadiologyQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $hospital_id = Auth::user()->hospital_id;
        $search = $request->query('search');
        $outpatient_number = $request->query('outpatient_number');
        
        $query = Patient::where('hospital_id', $hospital_id)->where('status', 'ACTIVE');

        if($search) {
            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $search . '%');
        }
        
        if($outpatient_number) {
            return $query->where('outpatient_number', $outpatient_number);
        }

        return $query->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'national_id' => 'required',
            'phone' => 'required'
        ]);
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $createdPatient = Patient::create($data);

        if($createdPatient){
            //DIRECT SERVICE PATIENT
            if($request->has('status')) {
                $consultationType = ConsultationType::where('hospital_id', $createdPatient->hospital_id)->where('name', 'General')->first();
                $session = [
                    'patient_id' => $createdPatient->id,
                    'hospital_id' => $createdPatient->hospital_id,
                    'created_by' => $createdPatient->created_by,
                    'patient_type' => 'DIRECT_SERVICE',
                    'primary_payment_method' => 'Cash',
                    'consultation_type' => $consultationType->id,
                    'consultation_fee' => 0,
                    'registration_fee' => 0
                ];
                $createdSession = PatientSession::create($session);

                $visit = [
                    'session_id' => $createdSession->id,
                    'created_by' => $createdPatient->created_by
                ];
                PatientVisit::create($visit);

                if ($data['station'] == 'pharmacy') { $queueModel = PharmacyQueue::class; }
                else if ($data['station'] == 'lab') { $queueModel = LabQueue::class; }
                else if ($data['station'] == 'radiology') { $queueModel = RadiologyQueue::class; }
                else if ($data['station'] == 'nurse') { $queueModel = NurseQueue::class; }

                $queue = [
                    'session_id' => $createdSession->id,
                    'hospital_id' => $createdPatient->hospital_id,
                    'created_by' => $createdPatient->created_by,
                ];
                $queueModel::create($queue);
            }

            return response($createdPatient, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $hospital_id = Auth::user()->hospital_id;

        $patient = Patient::where('hospital_id', $hospital_id)->where('id', $id)->first();
        if($patient){
            return response($patient);
        }
        else {
            return response(['message' => 'Patient not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'phone' => 'required'
        ]);
        $data = $request->all();

        $patient = Patient::find($id);
        $updatedPatient = $patient->update($data);

        if($updatedPatient){
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
        $patient = Patient::find($id);
        $patient->status = 'DELETED';
        
        if($patient->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
