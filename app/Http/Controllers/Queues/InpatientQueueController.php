<?php

namespace App\Http\Controllers\Queues;

use App\Models\Patient\PatientSession;
use App\Models\Patient\WardRound;
use App\Models\Queues\AdmissionQueue;
use App\Models\Queues\InpatientQueue;
use App\Models\Ward\Bed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InpatientQueueController extends QueueBaseController
{
    public function __construct(InpatientQueue $model)
    {
        parent::__construct($model);
    }

    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        return InpatientQueue::with(['session.patient', 'session.doctor', 'bed.ward', 'created_by'])->whereIn('status', ['ACTIVE', 'CLEARANCE'])
                            ->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function store(Request $request) {
        $request->validate([
            'session_id' => 'required',
            'bed_id' => 'required'
        ]);
        
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $queuePresent = InpatientQueue::where('session_id', $data['session_id'])->first();
        if(!$queuePresent) {
            $createdQueue = InpatientQueue::create($data);

            if($createdQueue) {
                //REMOVE FROM ADMISSION QUEUE
                $admissionQueue = AdmissionQueue::where('session_id', $data['session_id'])->first();
                AdmissionQueue::destroy($admissionQueue->id);

                //CHANGE SESSION TO INPATIENT
                $session = PatientSession::with('consultation')->find($data['session_id']);
                $session->patient_type = 'INPATIENT';
                $session->save();

                //CHANGE BED STATUS TO OCCUPIED
                $bed = Bed::find($data['bed_id']);
                $bed->status = 'OCCUPIED';
                $bed->save();

                //CREATE WARD ROUND
                WardRound::create([
                    'session_id' => $data['session_id'],
                    'bed_id' => $data['bed_id'],
                    'bed_price' => $bed->ward->price,
                    'nurse_price' => $session->consultation->inpatient_nurse_rate,
                    'doctor_price' => $session->consultation->inpatient_doctor_rate,
                    'created_by' => Auth::id()
                ]);

                return response(null, Response::HTTP_CREATED);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'Patient is already admitted to ward.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
