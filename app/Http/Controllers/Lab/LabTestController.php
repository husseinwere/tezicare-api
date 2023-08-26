<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabTest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return LabTest::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'test' => 'required',
            'price' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdTest = LabTest::create($data);

        if($createdTest){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'test' => 'required',
            'price' => 'required'
        ]);
        $data = $request->all();

        $test = LabTest::find($id);
        $updatedTest = $test->update($data);

        if($updatedTest){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if(LabTest::destroy($id)) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}