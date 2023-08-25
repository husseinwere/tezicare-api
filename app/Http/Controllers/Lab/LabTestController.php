<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabTest;
use Illuminate\Http\Request;
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

        return LabTest::create($data);
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
        $test->update($data);

        return $test;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return LabTest::destroy($id);
    }
}
