<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabTestParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LabTestParameterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $labTestId = $request->query('lab_test_id');

        return LabTestParameter::where('lab_test_id', $labTestId)->where('status', 'ACTIVE')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lab_test_id' => 'required',
            'name' => 'required',
            'unit' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdParameter = LabTestParameter::create($data);

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

        $parameter = LabTestParameter::find($id);
        $updatedParameter = $parameter->update($data);

        if($updatedParameter){
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
        $parameter = LabTestParameter::find($id);

        $parameter->status = "DELETED";

        if($parameter->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
