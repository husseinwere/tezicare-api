<?php

namespace App\Http\Controllers;

use App\Models\Inventory\NonPharmaceutical;
use App\Models\Inventory\Pharmaceutical;
use App\Models\Patient;
use App\Models\Patient\PatientDiagnosis;
use App\Models\Patient\PatientDrug;
use App\Models\Patient\PatientNonPharmaceutical;
use App\Models\Patient\PatientNursing;
use App\Models\Patient\PatientPrescription;
use App\Models\Patient\PatientRecommendation;
use App\Models\Patient\PatientSymptom;
use App\Models\Patient\PatientTest;
use App\Models\Patient\WardRound;
use App\Models\PatientSession;
use App\Models\Queues\DoctorQueue;
use App\Models\Queues\InpatientQueue;
use App\Models\Queues\LabQueue;
use App\Models\Queues\NurseQueue;
use App\Models\Queues\PharmacyQueue;
use App\Models\Queues\RadiologyQueue;
use App\Models\Queues\TriageQueue;
use App\Models\User;
use App\Models\Ward\Ward;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response as FacadesResponse;

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
            return response($createdSession, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    public function clearPatient(string $id)
    {
        TriageQueue::where('session_id', $id)->delete();
        DoctorQueue::where('session_id', $id)->delete();
        PharmacyQueue::where('session_id', $id)->delete();
        NurseQueue::where('session_id', $id)->delete();
        LabQueue::where('session_id', $id)->delete();
        RadiologyQueue::where('session_id', $id)->delete();
        InpatientQueue::where('session_id', $id)->delete();

        $session = PatientSession::find($id);
        $session->status = 'CLEARED';

        if($session->save()){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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

    public function printInvoice(string $id) {
        $patientSession = PatientSession::find($id);

        if($patientSession){
            $totalInvoiceAmount = 0;

            $consultation_fee = $patientSession->consultation_fee;
            $registration_fee = $patientSession->registration_fee;

            $totalInvoiceAmount += $consultation_fee;
            $totalInvoiceAmount += $registration_fee;

            $itemsHTML = "
                <tr class='item'>
                    <td style='width:35%;'>Registration fee</td>
                    <td style='width:20%; text-align:center;'>1</td>
                    <td style='width:25%; text-align:right;'>$registration_fee</td>
                    <td style='width:20%; text-align:right;'>$registration_fee</td>
                </tr>
                <tr class='item'>
                    <td style='width:35%;'>Consultation fee</td>
                    <td style='width:20%; text-align:center;'>1</td>
                    <td style='width:25%; text-align:right;'>$consultation_fee</td>
                    <td style='width:20%; text-align:right;'>$consultation_fee</td>
                </tr>
            ";

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

            if($patientSession->patient_type == 'INPATIENT') {
                //BED FEES
                $items = WardRound::where('session_id', $id)->get();
                foreach($items as $item) {
                    $ward = Ward::find($item->ward_id);
                    $totalInvoiceAmount += $item->bed_price;

                    $itemsHTML .= "
                        <tr class='item'>
                            <td style='width:35%;'>Bed Charges ($ward->name)</td>
                            <td style='width:20%; text-align:center;'>1</td>
                            <td style='width:25%; text-align:right;'>$item->bed_price</td>
                            <td style='width:20%; text-align:right;'>$item->bed_price</td>
                        </tr>
                    ";

                    if($item->doctor_price) {
                        $totalInvoiceAmount += $item->doctor_price;

                        $itemsHTML .= "
                            <tr class='item'>
                                <td style='width:35%;'>Ward Round (Doctor)</td>
                                <td style='width:20%; text-align:center;'>1</td>
                                <td style='width:25%; text-align:right;'>$item->doctor_price</td>
                                <td style='width:20%; text-align:right;'>$item->doctor_price</td>
                            </tr>
                        ";
                    }

                    if($item->nurse_price) {
                        $totalInvoiceAmount += $item->nurse_price;

                        $itemsHTML .= "
                            <tr class='item'>
                                <td style='width:35%;'>Ward Round (Nurse)</td>
                                <td style='width:20%; text-align:center;'>1</td>
                                <td style='width:25%; text-align:right;'>$item->nurse_price</td>
                                <td style='width:20%; text-align:right;'>$item->nurse_price</td>
                            </tr>
                        ";
                    }
                }
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

            //LAB FEES
            $items = PatientTest::join('lab_results', 'patient_tests.id', '=', 'lab_results.test_id')
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

            $patient = Patient::find($patientSession->patient_id);
            $patientString = "
                $patient->first_name $patient->last_name (OP No: $patient->id) <br>
                Gender: $patient->gender, Age: <br>
                $patient->phone, $patient->email<br>
                $patient->residence
            ";

            $invoiceHTML = "
                <style>
                    .top_rw{ background-color:#f4f4f4; }
                    button{ padding:5px 10px; font-size:14px;}
                    .invoice-box {
                        width: 100%;
                        margin: auto;
                        padding:10px;
                        border: 1px solid #eee;
                        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
                        font-size: 14px;
                        line-height: 24px;
                        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
                        color: #555;
                    }
                    .invoice-box table {
                        width: 100%;
                        line-height: inherit;
                        text-align: left;
                        border-bottom: solid 1px #ccc;
                    }
                    .invoice-box table td {
                        padding: 5px;
                        vertical-align:middle;
                    }
                    .invoice-box table tr td:nth-child(2) {
                        text-align: right;
                    }
                    .invoice-box table tr.top table td {
                        padding-bottom: 20px;
                    }
                    .invoice-box table tr.top table td.title {
                        font-size: 45px;
                        line-height: 45px;
                        color: #333;
                    }
                    .invoice-box table tr.information table td {
                        padding-bottom: 40px;
                    }
                    .invoice-box table tr.heading th {
                        background: #eee;
                        border-bottom: 1px solid #ddd;
                        font-weight: bold;
                        font-size:12px;
                    }
                    .invoice-box table tr.details td {
                        padding-bottom: 20px;
                    }
                    .invoice-box table tr.item td{
                        border-bottom: 1px solid #eee;
                    }
                    .invoice-box table tr.item.last td {
                        border-bottom: none;
                    }
                    .invoice-box table tr.total td:nth-child(2) {
                        border-top: 2px solid #eee;
                        font-weight: bold;
                    }
                    /** RTL **/
                    .rtl {
                        direction: rtl;
                        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
                    }
                    .rtl table {
                        text-align: right;
                    }
                    .rtl table tr td:nth-child(2) {
                        text-align: left;
                    }
                </style>
                
                <div class='invoice-box'>
                    <table cellpadding='0' cellspacing='0'>
                        <thead>
                            <tr>
                                <th colspan='2'></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class='top_rw'>
                                <td colspan='2'>
                                    <h2 style='margin-bottom: 0px;'> HOSPITAL INVOICE </h2>
                                    <span> Date: $patientSession->created_at </span>
                                </td>
                                <td  style='width:30%; margin-right: 10px;'>
                                    Invoice No: $patientSession->id
                                </td>
                            </tr>
                            <tr class='information'>
                                <td colspan='3'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan='2'>
                                                    <b> Invoice to: </b> <br>
                                                    $patientString
                                                </td>
                                                <td> <b> Invoice from: </b><br>
                                                    Hospital Name<br>
                                                    Hospital address<br>
                                                    Hospital email<br>
                                                    Hospital phone
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
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
                                                    TOTAL AMOUNT
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
                            <tr>
                                <td colspan='3'>
                                    <table cellspacing='0px' cellpadding='2px'>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td width='50%'>
                                                </td>
                                                <td>
                                                * This is a computer generated invoice and does not
                                                require a physical signature
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width='50%'>
                                                </td>
                                                <td>
                                                    <b> Patient Signature </b>
                                                    <br>
                                                    <br>
                                                    ...................................
                                                    <br>
                                                    <br>
                                                    <br>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ";

            // Create PDF instance
            $pdf = Pdf::loadHTML($invoiceHTML);
            
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
        $patientSession = PatientSession::find($id);

        if($patientSession){
            //DOCTOR
            $doctor = User::find($patientSession->doctor_id);
            $doctorName = "$doctor->first_name $doctor->last_name";

            //PATIENT SYMPTOMS
            $symptoms = PatientSymptom::where('session_id', $id)->pluck('symptom')->toArray();
            $symptomsString = implode(', ', $symptoms);

            //PATIENT DIAGNOSIS
            $diagnosis = PatientDiagnosis::where('session_id', $id)->pluck('diagnosis')->toArray();
            $diagnosisString = implode(', ', $diagnosis);

            $patient = Patient::find($patientSession->patient_id);
            $patientString = "
                $patient->first_name $patient->last_name (OP No: $patient->id) <br>
                Gender: $patient->gender, Age: <br>
                $patient->phone, $patient->email<br>
                $patient->residence
            ";

            //RECOMMENDATION
            $recommendations = PatientRecommendation::where('session_id', $id)->pluck('recommendation')->toArray();
            $recommendationsString = implode(', ', $recommendations);

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
            $tests = PatientTest::leftJoin('lab_results', 'patient_tests.id', '=', 'lab_results.test_id')
                                ->where('patient_tests.session_id', $patientSession->id)->where('patient_tests.status', 'ACTIVE')
                                ->select('patient_tests.*', 'lab_results.result', 'lab_results.description')
                                ->get();

            $testsString = "<ul>";
            foreach($tests as $test) {
                $testResult = "N/A";
                if($test->result) {
                    $testResult = "$test->result - $test->description";
                }

                $testsString .= "
                    <li>$test->test: <i>$testResult</i></li>
                ";
            }
            $testsString .= "</ul>";

            $summaryHTML = "
                <style>
                    .top_rw{ background-color:#f4f4f4; }
                    button{ padding:5px 10px; font-size:14px;}
                    .invoice-box {
                        width: 100%;
                        margin: auto;
                        padding:10px;
                        border: 1px solid #eee;
                        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
                        font-size: 14px;
                        line-height: 24px;
                        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
                        color: #555;
                    }
                    .invoice-box table {
                        width: 100%;
                        line-height: inherit;
                        text-align: left;
                        border-bottom: solid 1px #ccc;
                    }
                    .invoice-box table td {
                        padding: 5px;
                        vertical-align:middle;
                    }
                    .invoice-box table tr td:nth-child(2) {
                        text-align: right;
                    }
                    .invoice-box table tr.top table td {
                        padding-bottom: 20px;
                    }
                    .invoice-box table tr.top table td.title {
                        font-size: 45px;
                        line-height: 45px;
                        color: #333;
                    }
                    .invoice-box table tr.information table td {
                        padding-bottom: 40px;
                    }
                    .invoice-box table tr.heading th {
                        background: #eee;
                        border-bottom: 1px solid #ddd;
                        font-weight: bold;
                        font-size:12px;
                    }
                    .invoice-box table tr.details td {
                        padding-bottom: 20px;
                    }
                    .invoice-box table tr.item td{
                        border-bottom: 1px solid #eee;
                    }
                    .invoice-box table tr.item.last td {
                        border-bottom: none;
                    }
                    .invoice-box table tr.total td:nth-child(2) {
                        border-top: 2px solid #eee;
                        font-weight: bold;
                    }
                    /** RTL **/
                    .rtl {
                        direction: rtl;
                        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
                    }
                    .rtl table {
                        text-align: right;
                    }
                    .rtl table tr td:nth-child(2) {
                        text-align: left;
                    }
                </style>
                
                <div class='invoice-box'>
                    <table cellpadding='0' cellspacing='0'>
                        <thead>
                            <tr>
                                <th colspan='2'></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class='top_rw'>
                                <td colspan='2'>
                                    <h2 style='margin-bottom: 0px;'> DISCHARGE SUMMARY </h2>
                                    <span> Date: $patientSession->created_at </span>
                                </td>
                                <td  style='width:30%; margin-right: 10px;'>
                                    Invoice No: $patientSession->id
                                </td>
                            </tr>
                            <tr class='information'>
                                <td colspan='3'>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan='2'>
                                                    <b> Patient: </b> <br>
                                                    $patientString
                                                </td>
                                                <td>
                                                    Hospital Name<br>
                                                    Hospital address<br>
                                                    Hospital email<br>
                                                    Hospital phone
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='3'>
                                    <p>
                                        <b>SYMPTOMS: </b>
                                        $symptomsString
                                    </p>
                                    <p>
                                        <b>DIAGNOSIS: </b>
                                        $diagnosisString
                                    </p>
                                    <p>
                                        <b>LAB REPORT: </b>
                                        $testsString
                                    </p>
                                    <p>
                                        <b>PRESCRIPTION: </b>
                                        $prescriptionString
                                    </p>
                                    <p>
                                        <b>TREATMENT: </b>
                                        $drugsString
                                    </p>
                                    <p>
                                        <b>RECOMMENDATIONS: </b>
                                        $recommendationsString
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan='3'>
                                    <table cellspacing='0px' cellpadding='2px'>
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td width='50%'>
                                                </td>
                                                <td>
                                                * This is a computer generated invoice and does not
                                                require a physical signature
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width='50%'>
                                                </td>
                                                <td>
                                                    <b> Doctor Signature </b>
                                                    <br>
                                                    $doctorName
                                                    <br><br>
                                                    ...................................
                                                    <br>
                                                    <br>
                                                    <br>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ";

            // Create PDF instance
            $pdf = Pdf::loadHTML($summaryHTML);
            
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
}
