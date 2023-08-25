<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabTest;
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

        $tests = PatientTest::where('session_id', $sessionId)->get();

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

        PatientTest::create($data);

        return response(null, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $test = PatientTest::find($id);
        $test->update($data);

        return response(null, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $test = PatientTest::find($id);

        if($test['payment_status'] == "PAID" || $test['status'] == "CLEARED") {
            return response(['error' => 'You cannot delete paid for or cleared tests.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            return PatientTest::destroy($id);
        }
    }
}
