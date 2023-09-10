<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $search = $request->query('search');
        $searchId = $request->query('searchId');
        
        if($search) {
            return Patient::where('status', 'ACTIVE')
                            ->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $search . '%')
                            ->paginate($pageSize, ['*'], 'page', $pageIndex);
        }
        else if($searchId) {
            return Patient::where('id', $searchId)->paginate();
        }
        else {
            return Patient::where('status', 'ACTIVE')->paginate($pageSize, ['*'], 'page', $pageIndex);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'phone' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdPatient = Patient::create($data);

        if($createdPatient){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $patient = Patient::find($id);
        if($patient){
            return response($patient);
        }
        else {
            return response(['message' => 'Patient not found'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'phone' => 'required'
        ]);
        $data = $request->all();

        $patient = Patient::find($id);
        $updatedPatient = $patient->update($data);

        if($updatedPatient){
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
        $patient = Patient::find($id);
        $patient->status = 'DELETED';
        
        if($patient->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
