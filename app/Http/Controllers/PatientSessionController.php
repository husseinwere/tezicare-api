<?php

namespace App\Http\Controllers;

use App\Models\Patient\PatientDiagnosis;
use App\Models\PatientSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientSessionController extends Controller
{
    public function index(Request $request)
    {
        $patient_id = $request->input('patient_id');

        return PatientSession::where('patient_id', $patient_id)->limit(10)->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required',
            'consultation_type' => 'required'
        ]);
        $data = $request->all();

        $existingRecord = PatientSession::where('patient_id', $data['patient_id'])->where('status', 'ACTIVE')->first();

        if ($existingRecord) {
            return response(['message' => 'A session has already been started with this patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data['created_by'] = Auth::id();

        return PatientSession::create($data);
    }

    public function show(string $id)
    {
        return PatientSession::leftJoin('users', 'users.id', '=', 'patient_sessions.doctor_id')
                            ->select('patient_sessions.*', DB::raw('CONCAT(users.first_name, " ", users.last_name) as doctor'))
                            ->where('patient_sessions.id', $id)->first();
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $session = PatientSession::find($id);
        $session->update($data);

        return $session;
    }

    public function discharge(string $id)
    {
        //DELETE PATIENT FROM QUEUES (INCASE iTS NECESSARY)

        //UPDATE DISCHARGE DATE TIME
        $currentDateTime = Carbon::now();

        $session = PatientSession::find($id);
        $session->discharge = $currentDateTime;
        $session->save();

        //SEND TO CLEARANCE QUEUE AT CASHIER

        return $session;
    }

    public function destroy(string $id)
    {
        $session = PatientSession::find($id);
        $session->status = 'DELETED';
        $session->save();

        return $session;
    }

    //REPORTS
    public function getPatientStats(string $patient_id)
    {
        $outpatientCount = PatientSession::where('patient_id', $patient_id)->where('patient_type', 'OUTPATIENT')->count();
        $inpatientCount = PatientSession::where('patient_id', $patient_id)->where('patient_type', 'INPATIENT')->count();

        return [
            'outpatient' => $outpatientCount,
            'inpatient' => $inpatientCount
        ];
    }

    public function getVisitStats(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $date = $request->input('date');

        $query = PatientSession::where('status', 'CLEARED');
        if ($year) { $query->whereYear('created_at', $year); }
        if ($month) { $query->whereMonth('created_at', $month); }
        if ($date) { $query->whereDate('created_at', $date); }
        $totalPatientCount = $query->count();

        $query = PatientSession::where('patient_sessions.patient_type', 'OUTPATIENT')->where('patient_sessions.status', 'CLEARED');
        if ($year) { $query->whereYear('created_at', $year); }
        if ($month) { $query->whereMonth('created_at', $month); }
        if ($date) { $query->whereDate('created_at', $date); }
        $totalOutpatientCount = $query->count();

        $totalInpatientCount = $totalPatientCount - $totalOutpatientCount;

        $query = PatientSession::where('patient_sessions.patient_type', 'OUTPATIENT')->where('patient_sessions.status', 'CLEARED')
                                ->join('patients', 'patients.id', '=', 'patient_sessions.patient_id')
                                ->where('patients.gender', 'Male');
        if ($year) { $query->whereYear('patient_sessions.created_at', $year); }
        if ($month) { $query->whereMonth('patient_sessions.created_at', $month); }
        if ($date) { $query->whereDate('patient_sessions.created_at', $date); }
        $maleOutpatientCount = $query->count();

        $femaleOutpatientCount = $totalOutpatientCount - $maleOutpatientCount;

        $query = PatientSession::where('patient_sessions.patient_type', 'OUTPATIENT')->where('patient_sessions.status', 'CLEARED')
                                ->join('patients', 'patients.id', '=', 'patient_sessions.patient_id')
                                ->where(DB::raw('YEAR(CURRENT_DATE) - YEAR(patients.dob)'), '>', 5);
        if ($year) { $query->whereYear('patient_sessions.created_at', $year); }
        if ($month) { $query->whereMonth('patient_sessions.created_at', $month); }
        if ($date) { $query->whereDate('patient_sessions.created_at', $date); }
        $over5OutpatientCount = $query->count();

        $under5OutpatientCount = $totalOutpatientCount - $over5OutpatientCount;

        if($date) { $startDate = date('Y-m-d', strtotime("$year-$month-$date")); }
        else { $startDate = date('Y-m-d', strtotime("$year-$month-01")); }

        $query = PatientSession::where('patient_sessions.patient_type', 'OUTPATIENT')->where('patient_sessions.status', 'CLEARED')
                                ->join('patients', 'patients.id', '=', 'patient_sessions.patient_id')
                                ->where('patients.created_at', '>', $startDate);
        if ($year) { $query->whereYear('patient_sessions.created_at', $year); }
        if ($month) { $query->whereMonth('patient_sessions.created_at', $month); }
        if ($date) { $query->whereDate('patient_sessions.created_at', $date); }
        $newOutpatientCount = $query->count();

        $revisitOutpatientCount = $totalOutpatientCount - $newOutpatientCount;

        $query = PatientSession::where('patient_sessions.patient_type', 'INPATIENT')->where('patient_sessions.status', 'CLEARED')
                                ->join('patients', 'patients.id', '=', 'patient_sessions.patient_id')
                                ->where('patients.gender', 'Male');
        if ($year) { $query->whereYear('patient_sessions.created_at', $year); }
        if ($month) { $query->whereMonth('patient_sessions.created_at', $month); }
        if ($date) { $query->whereDate('patient_sessions.created_at', $date); }
        $maleInpatientCount = $query->count();

        $femaleInpatientCount = $totalInpatientCount - $maleInpatientCount;

        $query = PatientSession::where('patient_sessions.patient_type', 'INPATIENT')->where('patient_sessions.status', 'CLEARED')
                                ->join('patients', 'patients.id', '=', 'patient_sessions.patient_id')
                                ->where(DB::raw('YEAR(CURRENT_DATE) - YEAR(patients.dob)'), '>', 5);
        if ($year) { $query->whereYear('patient_sessions.created_at', $year); }
        if ($month) { $query->whereMonth('patient_sessions.created_at', $month); }
        if ($date) { $query->whereDate('patient_sessions.created_at', $date); }
        $over5InpatientCount = $query->count();

        $under5InpatientCount = $totalInpatientCount - $over5InpatientCount;

        $query = PatientSession::where('patient_sessions.patient_type', 'INPATIENT')->where('patient_sessions.status', 'CLEARED')
                                ->join('patients', 'patients.id', '=', 'patient_sessions.patient_id')
                                ->where('patients.created_at', '>', $startDate);
        if ($year) { $query->whereYear('patient_sessions.created_at', $year); }
        if ($month) { $query->whereMonth('patient_sessions.created_at', $month); }
        if ($date) { $query->whereDate('patient_sessions.created_at', $date); }
        $newInpatientCount = $query->count();

        $revisitInpatientCount = $totalInpatientCount - $newInpatientCount;

        //DIAGNOSIS
        $query = PatientDiagnosis::whereYear('created_at', $year)->whereMonth('created_at', $month);
        if ($date) { $query->whereDate('created_at', $date); }
        
        $diagnosisCounts = $query->select('diagnosis', DB::raw('COUNT(*) as count'))
                                ->groupBy('diagnosis')
                                ->orderBy('count', 'desc')
                                ->get();

        return [
            'outpatient' => [
                'total' => $totalOutpatientCount,
                'male' => $maleOutpatientCount,
                'female' => $femaleOutpatientCount,
                'under5' => $under5OutpatientCount,
                'over5' => $over5OutpatientCount,
                'new' => $newOutpatientCount,
                'revisit' => $revisitOutpatientCount
            ],
            'inpatient' => [
                'total' => $totalInpatientCount,
                'male' => $maleInpatientCount,
                'female' => $femaleInpatientCount,
                'under5' => $under5InpatientCount,
                'over5' => $over5InpatientCount,
                'new' => $newInpatientCount,
                'revisit' => $revisitInpatientCount
            ],
            'diagnosis' => $diagnosisCounts
        ];
    }
}
