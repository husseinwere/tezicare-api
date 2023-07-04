<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
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
        
        return Patient::where('status', 'ACTIVE')->paginate($pageSize, ['*'], 'page', $pageIndex);
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

        return Patient::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Patient::find($id);
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
        $patient->update($data);

        return $patient;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $patient = Patient::find($id);
        $patient->status = 'DELETED';
        $patient->save();

        return $patient;
    }

    /**
     * Search the specified resource by name.
     */
    public function searchByName(Request $request, string $name)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        return Patient::where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $name . '%')->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Search the specified resource by id.
     */
    public function searchById(string $id)
    {
        return Patient::where('id', $id)->paginate();
    }
}
