<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\PatientTestParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PatientTestParameterController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lab_result_id' => 'required',
            'lab_test_parameter_id' => 'required|exists:lab_test_parameters,id',
            'value' => 'required'
        ]);
        $data = $request->all();

        $createdParameter = PatientTestParameter::create($data);

        if($createdParameter){
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

        $parameter = PatientTestParameter::find($id);

        $updatedParameter = $parameter->update($data);

        if($updatedParameter){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
