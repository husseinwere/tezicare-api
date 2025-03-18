<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment\Appointment;
use App\Models\Billing\InvoiceAddition;
use App\Models\Hospital\Configuration;
use App\Models\Inventory\NonPharmaceutical;
use App\Models\Inventory\Pharmaceutical;
use App\Models\Patient\Patient;
use App\Models\Patient\PatientDentalService;
use App\Models\Patient\PatientDiagnosis;
use App\Models\Patient\PatientDrug;
use App\Models\Patient\PatientNonPharmaceutical;
use App\Models\Patient\PatientNursing;
use App\Models\Patient\PatientPrescription;
use App\Models\Patient\PatientRecommendation;
use App\Models\Patient\PatientSession;
use App\Models\Patient\PatientSymptom;
use App\Models\Patient\PatientTest;
use App\Models\Patient\PatientVisit;
use App\Models\Patient\WardRound;
use App\Models\Queues\ClearanceQueue;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\InpatientQueue;
use App\Models\Queues\LabQueue;
use App\Models\Queues\NurseQueue;
use App\Models\Queues\PharmacyQueue;
use App\Models\Queues\RadiologyQueue;
use App\Models\Queues\TriageQueue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Response as FacadesResponse;

class PatientSessionController extends Controller
{
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $patient_id = $request->input('patient_id');
        $status = $request->input('status');
        $patient_type = $request->input('patient_type');
        $consultation_type = $request->input('consultation_type');
        $startAt = $request->input('startAt');
        $endAt = $request->input('endAt');

        $query = PatientSession::with(['patient', 'consultation']);

        if($patient_id) {
            $query->where('patient_id', $patient_id);
        }

        if($status) {
            $query->where('status', $status);
        }

        if($patient_type) {
            $query->where('patient_type', $patient_type);
        }

        if($consultation_type) {
            $query->where('consultation_type', $consultation_type);
        }

        if($startAt && $endAt) {
            $startAt = Carbon::createFromFormat('Y-m-d', $startAt)->startOfDay();
            $endAt = Carbon::createFromFormat('Y-m-d', $endAt)->endOfDay();

            $query->whereBetween('created_at', [$startAt, $endAt]);
        }

        return $query->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required',
            'consultation_type' => 'required',
            'consultation_fee' => 'required',
            'registration_fee' => 'required'
        ]);
        $data = $request->all();

        $existingRecord = PatientSession::where('patient_id', $data['patient_id'])->where('status', 'ACTIVE')->first();

        if ($existingRecord) {
            return response(['message' => 'A session has already been started with this patient.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data['created_by'] = Auth::id();

        $createdSession = PatientSession::create($data);

        if($createdSession){
            $visit = [
                'session_id' => $createdSession->id,
                'created_by' => Auth::id()
            ];
            PatientVisit::create($visit);

            return response($createdSession, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(string $id)
    {
        return PatientSession::with(['patient', 'consultation', 'doctor'])->where('patient_sessions.id', $id)->first();
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $session = PatientSession::find($id);
        $session->update($data);

        return $session;
    }

    public function clearPatient(string $id)
    {
        TriageQueue::where('session_id', $id)->delete();
        DoctorQueue::where('session_id', $id)->delete();
        PharmacyQueue::where('session_id', $id)->delete();
        NurseQueue::where('session_id', $id)->delete();
        LabQueue::where('session_id', $id)->delete();
        RadiologyQueue::where('session_id', $id)->delete();
        InpatientQueue::where('session_id', $id)->delete();
        ClearanceQueue::where('session_id', $id)->delete();

        $session = PatientSession::find($id);
        $session->status = 'CLEARED';

        if(!$session->discharged) $session->discharged = Carbon::now();

        $visitCount = PatientVisit::where('session_id', $id)->count();
        if($visitCount > 1) {
            $session->discharged = Carbon::now();
        }

        if($session->save()){
            $visit = PatientVisit::where('session_id', $session->id)->latest()->first();
            $visit->status = 'CLEARED';
            $visit->discharged = Carbon::now();
            $visit->save();

            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(string $id)
    {
        //not functional now
        $session = PatientSession::find($id);
        //add logic to delete patient visit
        $session->status = 'DELETED';
        $session->save();

        return $session;
    }

    //REPORTS
    public function dashboardToday()
    {
        $visitsToday = PatientSession::whereDate('created_at', Carbon::today())->count();
        $activeSessions = PatientSession::where('status', 'ACTIVE')->count();
        $inpatients = PatientSession::where('patient_type', 'INPATIENT')->where('status', 'ACTIVE')->count();
        $patients = Patient::where('status', 'ACTIVE')->count();
        $appointments = Appointment::where('status', 'ACTIVE')->whereDate('appointment_date', Carbon::today())->count();

        return [
            'visits_today' => $visitsToday,
            'active_sessions' => $activeSessions,
            'inpatients' => $inpatients,
            'patients' => $patients,
            'appointments' => $appointments
        ];
    }

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

    public function printInvoice(string $id) {
        $patientSession = PatientSession::with(['patient', 'doctor'])->find($id);

        if($patientSession){
            $patientSession->patient->age = $this->calculateAge($patientSession->patient->dob);

            $totalInvoiceAmount = 0;

            $consultation_fee = $patientSession->consultation_fee;
            $registration_fee = $patientSession->registration_fee;

            $totalInvoiceAmount += $consultation_fee;
            $totalInvoiceAmount += $registration_fee;

            $itemsHTML = "";

            if($registration_fee > 0) {
                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>Registration fee</td>
                        <td style='width:20%; text-align:center;'>1</td>
                        <td style='width:25%; text-align:right;'>$registration_fee</td>
                        <td style='width:20%; text-align:right;'>$registration_fee</td>
                    </tr>
                ";
            }
            if($consultation_fee > 0) {
                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>Consultation fee</td>
                        <td style='width:20%; text-align:center;'>1</td>
                        <td style='width:25%; text-align:right;'>$consultation_fee</td>
                        <td style='width:20%; text-align:right;'>$consultation_fee</td>
                    </tr>
                ";
            }

            //INVOICE ADDITIONS: GENERAL
            $items = InvoiceAddition::where('session_id', $id)->where('category', 'GENERAL')->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->rate</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            if($patientSession->patient_type == 'INPATIENT') {
                //BED FEES
                $bedRecords = WardRound::with('bed.ward')
                                        ->select('bed_id', 'bed_price', DB::raw('COUNT(*) as quantity'), DB::raw('SUM(bed_price) as total'))
                                        ->where('session_id', $id)
                                        ->groupBy('bed_id', 'bed_price')
                                        ->get();
                foreach($bedRecords as $item) {
                    $ward = $item->bed->ward;
                    $totalInvoiceAmount += $item->total;

                    $itemsHTML .= "
                        <tr class='item'>
                            <td style='width:35%;'>Bed Charges ($ward->name)</td>
                            <td style='width:20%; text-align:center;'>$item->quantity</td>
                            <td style='width:25%; text-align:right;'>$item->bed_price</td>
                            <td style='width:20%; text-align:right;'>$item->total</td>
                        </tr>
                    ";
                }

                //DOCTOR ROUND FEES
                $doctorRecords = WardRound::select('doctor_price', DB::raw('COUNT(*) as quantity'), DB::raw('SUM(doctor_price) as total'))
                                        ->where('session_id', $id)
                                        ->groupBy('doctor_price')
                                        ->get();
                foreach($doctorRecords as $item) {
                    $totalInvoiceAmount += $item->total;

                    $itemsHTML .= "
                        <tr class='item'>
                            <td style='width:35%;'>Ward Rounds (Doctor)</td>
                            <td style='width:20%; text-align:center;'>$item->quantity</td>
                            <td style='width:25%; text-align:right;'>$item->doctor_price</td>
                            <td style='width:20%; text-align:right;'>$item->total</td>
                        </tr>
                    ";
                }

                //NURSE ROUND FEES
                $nurseRecords = WardRound::select('nurse_price', DB::raw('COUNT(*) as quantity'), DB::raw('SUM(nurse_price) as total'))
                                        ->where('session_id', $id)
                                        ->groupBy('nurse_price')
                                        ->get();
                foreach($nurseRecords as $item) {
                    $totalInvoiceAmount += $item->total;

                    $itemsHTML .= "
                        <tr class='item'>
                            <td style='width:35%;'>Ward Rounds (Nurse)</td>
                            <td style='width:20%; text-align:center;'>$item->quantity</td>
                            <td style='width:25%; text-align:right;'>$item->nurse_price</td>
                            <td style='width:20%; text-align:right;'>$item->total</td>
                        </tr>
                    ";
                }

                //INVOICE ADDITIONS: INPATIENT
                $items = InvoiceAddition::where('session_id', $id)->where('category', 'INPATIENT')->where('status', 'ACTIVE')->get();
                foreach($items as $item) {
                    $totalPrice = $item->quantity * $item->rate;
                    $totalInvoiceAmount += $totalPrice;

                    $itemsHTML .= "
                        <tr class='item'>
                            <td style='width:35%;'>$item->name</td>
                            <td style='width:20%; text-align:center;'>$item->quantity</td>
                            <td style='width:25%; text-align:right;'>$item->rate</td>
                            <td style='width:20%; text-align:right;'>$totalPrice</td>
                        </tr>
                    ";
                }
            }

            //NURSE FEES
            $items = PatientNursing::where('session_id', $id)->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalInvoiceAmount += $item->price;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->service</td>
                        <td style='width:20%; text-align:center;'>1</td>
                        <td style='width:25%; text-align:right;'>$item->price</td>
                        <td style='width:20%; text-align:right;'>$item->price</td>
                    </tr>
                ";
            }

            //INVOICE ADDITIONS: NURSE
            $items = InvoiceAddition::where('session_id', $id)->where('category', 'NURSE')->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->rate</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            //DENTAL FEES
            $items = PatientDentalService::with('dental_service')->where('session_id', $id)->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalInvoiceAmount += $item->price;
                $service = $item->dental_service;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$service->name</td>
                        <td style='width:20%; text-align:center;'>1</td>
                        <td style='width:25%; text-align:right;'>$item->price</td>
                        <td style='width:20%; text-align:right;'>$item->price</td>
                    </tr>
                ";
            }

            //INVOICE ADDITIONS: DENTAL
            $items = InvoiceAddition::where('session_id', $id)->where('category', 'DENTAL')->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->rate</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            //NON-PHARMACEUTICALS
            $items = PatientNonPharmaceutical::where('session_id', $id)->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $nonPharmaceutical = NonPharmaceutical::find($item->non_pharmaceutical_id);
                $totalPrice = $item->quantity * $item->unit_price;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$nonPharmaceutical->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->unit_price</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            //INVOICE ADDITIONS: NON_PHARMACEUTICALS
            $items = InvoiceAddition::where('session_id', $id)->where('category', 'NON_PHARMACEUTICALS')->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->rate</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            //LAB FEES
            $items = PatientTest::join('lab_results', 'patient_tests.id', '=', 'lab_results.patient_test_id')
                                ->where('session_id', $id)->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalInvoiceAmount += $item->price;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->test</td>
                        <td style='width:20%; text-align:center;'>1</td>
                        <td style='width:25%; text-align:right;'>$item->price</td>
                        <td style='width:20%; text-align:right;'>$item->price</td>
                    </tr>
                ";
            }

            //INVOICE ADDITIONS: LAB
            $items = InvoiceAddition::where('session_id', $id)->where('category', 'LAB')->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->rate</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            //PHARMACY FEES
            $items = PatientDrug::where('session_id', $id)->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $pharmaceutical = Pharmaceutical::find($item->drug_id);
                $totalPrice = $item->quantity * $item->unit_price;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$pharmaceutical->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->unit_price</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            //INVOICE ADDITIONS: PHARMACEUTICALS
            $items = InvoiceAddition::where('session_id', $id)->where('category', 'PHARMACEUTICALS')->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->rate</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            //INVOICE ADDITIONS: OTHER
            $items = InvoiceAddition::where('session_id', $id)->where('category', 'OTHER')->where('status', 'ACTIVE')->get();
            foreach($items as $item) {
                $totalPrice = $item->quantity * $item->rate;
                $totalInvoiceAmount += $totalPrice;

                $itemsHTML .= "
                    <tr class='item'>
                        <td style='width:35%;'>$item->name</td>
                        <td style='width:20%; text-align:center;'>$item->quantity</td>
                        <td style='width:25%; text-align:right;'>$item->rate</td>
                        <td style='width:20%; text-align:right;'>$totalPrice</td>
                    </tr>
                ";
            }

            $content = "
                <tr>
                    <td colspan='3'>
                        <table cellspacing='0px' cellpadding='2px'>
                            <thead>
                                <tr class='heading'>
                                    <th style='width:35%;'>
                                        ITEM
                                    </th>
                                    <th style='width:20%; text-align:center;'>
                                        QTY.
                                    </th>
                                    <th style='width:25%; text-align:right;'>
                                        PRICE
                                    </th>
                                    <th style='width:20%; text-align:right;'>
                                        TOTAL
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                $itemsHTML
                                <tr class='item'>
                                    <td style='width:35%;'></td>
                                    <td style='width:20%; text-align:center;'></td>
                                    <td style='width:25%; text-align:right;'><b> Grand Total </b></td>
                                    <td style='width:20%; text-align:right;'>$totalInvoiceAmount</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            ";

            $hospital = Configuration::first();

            // Create PDF instance
            $pdf = Pdf::loadView('docs.document', [
                'title' => 'HOSPITAL INVOICE',
                'patientSession' => $patientSession,
                'hospital' => $hospital,
                'content' => $content,
            ]);
            
            $response = FacadesResponse::make($pdf->stream(), Response::HTTP_OK);
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

            return $response;
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printDischargeSummary(string $id) {
        $patientSession = PatientSession::with(['patient', 'doctor'])->find($id);

        if($patientSession) {
            $patientSession->patient->age = $this->calculateAge($patientSession->patient->dob);

            //PATIENT SYMPTOMS
            $symptoms = PatientSymptom::where('session_id', $id)->pluck('symptom')->toArray();
            $symptomsString = "<ul>";
            foreach($symptoms as $symptom) {
                $symptomsString .= "
                    <li>$symptom</li>
                ";
            }
            $symptomsString .= "</ul>";

            //PATIENT DIAGNOSIS
            $diagnosis = PatientDiagnosis::where('session_id', $id)->pluck('diagnosis')->toArray();
            $diagnosisString = "<ul>";
            foreach($diagnosis as $d) {
                $diagnosisString .= "
                    <li>$d</li>
                ";
            }
            $diagnosisString .= "</ul>";

            //RECOMMENDATION
            $recommendations = PatientRecommendation::where('session_id', $id)->pluck('recommendation')->toArray();
            $recommendationsString = "<ul>";
            foreach($recommendations as $recommendation) {
                $recommendationsString .= "
                    <li>$recommendation</li>
                ";
            }
            $recommendationsString .= "</ul>";

            //DOCTOR PRESCRIPTION
            $prescriptions = PatientPrescription::where('session_id', $id)->get();
            $prescriptionString = "<ul>";
            foreach($prescriptions as $prescription) {
                $prescriptionString .= "
                    <li>$prescription->drug: <i>$prescription->dosage</i></li>
                ";
            }
            $prescriptionString .= "</ul>";

            //DRUGS
            $drugs = PatientDrug::with('pharmaceutical')->where('session_id', $id)->get();
            $drugsString = "<ul>";
            foreach($drugs as $drug) {
                $pharmaceutical = $drug->pharmaceutical;
                $drugsString .= "
                    <li>$pharmaceutical->name: <i>$drug->dosage</i></li>
                ";
            }
            $drugsString .= "</ul>";

            //LAB REPORT
            $tests = PatientTest::with(['lab_test', 'lab_result'])->where('session_id', $patientSession->id)->where('status', 'ACTIVE')->get();

            $testsString = "<ul>";
            foreach($tests as $test) {
                $testResult = "Not Done";
                $lab_test = $test->lab_test;
                $lab_result = $test->lab_result;
                if($lab_result) {
                    $description = $lab_result->description ? $lab_result->description : "";
                    $testResult = "<i>$lab_result->result</i> <div>$description</div>";
                }

                $testsString .= "
                    <li><b>$lab_test->test: </b>$testResult</li>
                ";
            }
            $testsString .= "</ul>";
            $content = "
                <tr>
                    <td colspan='3'>
                        <p>
                            <b>SYMPTOMS: </b> <br>
                            $symptomsString
                        </p>
                        <p>
                            <b>DIAGNOSIS: </b> <br>
                            $diagnosisString
                        </p>
                        <p>
                            <b>LAB REPORT: </b> <br>
                            $testsString
                        </p>
                        <p>
                            <b>PRESCRIPTION: </b> <br>
                            $prescriptionString
                        </p>
                        <p>
                            <b>TREATMENT: </b> <br>
                            $drugsString
                        </p>
                        <p>
                            <b>RECOMMENDATIONS: </b> <br>
                            $recommendationsString
                        </p>
                    </td>
                </tr>
            ";

            $hospital = Configuration::first();

            // Create PDF instance
            $pdf = Pdf::loadView('docs.document', [
                'title' => 'DISCHARGE SUMMARY',
                'patientSession' => $patientSession,
                'hospital' => $hospital,
                'content' => $content,
            ]);
            
            $response = FacadesResponse::make($pdf->stream(), Response::HTTP_OK);
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

            return $response;
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printPrescription(string $id) {
        $patientSession = PatientSession::with(['patient', 'doctor'])->find($id);

        if($patientSession) {
            $patientSession->patient->age = $this->calculateAge($patientSession->patient->dob);

            //DOCTOR PRESCRIPTION
            $prescriptions = PatientPrescription::where('session_id', $id)->get();
            $prescriptionString = "<ul>";
            foreach($prescriptions as $prescription) {
                $prescriptionString .= "
                    <li>$prescription->drug: <i>$prescription->dosage</i></li>
                ";
            }
            $prescriptionString .= "</ul>";

            //RECOMMENDATION
            $recommendations = PatientRecommendation::where('session_id', $id)->pluck('recommendation')->toArray();
            $recommendationsString = "<ul>";
            foreach($recommendations as $recommendation) {
                $recommendationsString .= "
                    <li>$recommendation</li>
                ";
            }
            $recommendationsString .= "</ul>";

            $content = "
                <tr>
                    <td colspan='3'>
                        <p>
                            $prescriptionString
                        </p>
                        <p>
                            <b>RECOMMENDATIONS: </b> <br>
                            $recommendationsString
                        </p>
                    </td>
                </tr>
            ";

            $hospital = Configuration::first();

            // Create PDF instance
            $pdf = Pdf::loadView('docs.document', [
                'title' => 'PRESCRIPTION',
                'patientSession' => $patientSession,
                'hospital' => $hospital,
                'content' => $content,
            ]);
            
            $response = FacadesResponse::make($pdf->stream(), Response::HTTP_OK);
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

            return $response;
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printLabReport(string $id) {
        $patientSession = PatientSession::with(['patient', 'doctor'])->find($id);

        if($patientSession) {
            $patientSession->patient->age = $this->calculateAge($patientSession->patient->dob);

            //LAB REPORT
            $tests = PatientTest::with(['lab_test', 'lab_result'])->where('session_id', $patientSession->id)->where('status', 'ACTIVE')->get();

            $testsString = "<ul>";
            foreach($tests as $test) {
                $testResult = "Not Done";
                $lab_test = $test->lab_test;
                $lab_result = $test->lab_result;
                if($lab_result) {
                    $description = $lab_result->description ? $lab_result->description : "";
                    $testResult = "<i>$lab_result->result</i> <div>$description</div>";
                }

                $testsString .= "
                    <li><b>$lab_test->test: </b>$testResult</li>
                ";
            }
            $testsString .= "</ul>";
            $content = "
                <tr>
                    <td colspan='3'>
                        <p>
                            $testsString
                        </p>
                    </td>
                </tr>
            ";

            $hospital = Configuration::first();

            // Create PDF instance
            $pdf = Pdf::loadView('docs.document', [
                'title' => 'LAB REPORT',
                'patientSession' => $patientSession,
                'hospital' => $hospital,
                'content' => $content,
            ]);
            
            $response = FacadesResponse::make($pdf->stream(), Response::HTTP_OK);
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

            return $response;
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function calculateAge($dob)
    {
        $dob = new DateTime($dob);
        $today = new DateTime('today');
        $age = $dob->diff($today)->y;

        return $age;
    }
}
