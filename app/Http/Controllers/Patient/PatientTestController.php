<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabTest;
use App\Models\Patient\PatientTest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        $tests = PatientTest::join('lab_tests', 'lab_tests.id', '=', 'patient_tests.test_id')
                            ->leftJoin('lab_results', 'patient_tests.id', '=', 'lab_results.test_id')
                            ->join('users as doctor', 'doctor.id', '=', 'patient_tests.created_by')
                            ->leftJoin('users as lab', 'lab.id', '=', 'lab_results.created_by')
                            ->select('patient_tests.id', 'patient_tests.test', 'patient_tests.price', 'patient_tests.additional_info', 'patient_tests.payment_status', 'patient_tests.created_at',
                                    'lab_tests.lab', 'lab_results.id as result_id', 'lab_results.result', 'lab_results.description',
                                    DB::raw('CONCAT(doctor.first_name, " ", doctor.last_name) as requested_by'),
                                    DB::raw('CONCAT(lab.first_name, " ", lab.last_name) as results_by'))
                            ->where('patient_tests.session_id', $sessionId)
                            ->get();

        return $tests;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'test_id' => 'required'
        ]);

        $data = $request->all();

        $test = LabTest::find($data['test_id']);

        $data['test'] = $test['test'];
        $data['price'] = $test['price'];
        $data['created_by'] = Auth::id();

        $createdTest = PatientTest::create($data);

        if($createdTest){
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

        $test = PatientTest::find($id);

        $updatedTest = $test->update($data);

        if($updatedTest){
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
        $test = PatientTest::find($id);

        if($test['payment_status'] == "PAID" || $test['status'] == "CLEARED") {
            return response(['message' => 'You cannot delete paid for or cleared tests.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            if(PatientTest::destroy($id)) {
                return response(null, Response::HTTP_NO_CONTENT);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
