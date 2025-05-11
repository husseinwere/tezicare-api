<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabTest;
use App\Models\Patient\PatientSession;
use App\Models\Patient\PatientTest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PatientTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        return PatientTest::with(['lab_test', 'lab_result.lab_result_uploads', 'lab_result.created_by', 'created_by'])
                            ->where('patient_tests.session_id', $sessionId)->get();
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

        $session = PatientSession::find($data['session_id']);
        $test = LabTest::with('prices')->find($data['test_id']);

        $data['test_name'] = $test['test'];
        $data['price'] = $test['price'];
        $data['created_by'] = Auth::id();

        $prices = $test->prices;
        if($session->insurance_id) {
            $insurancePrice = $prices->where('insurance_id', $session->insurance_id)->first();
            if($insurancePrice) {
                $data['price'] = $insurancePrice['price'];
            }
        }

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
