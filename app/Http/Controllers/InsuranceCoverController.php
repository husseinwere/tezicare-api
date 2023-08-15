<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InsuranceCover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InsuranceCoverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   
        return InsuranceCover::where('status', 'ACTIVE')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'insurance' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        return InsuranceCover::create($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'insurance' => 'required'
        ]);
        $data = $request->all();

        $cover = InsuranceCover::find($id);
        $cover->update($data);

        return $cover;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cover = InsuranceCover::find($id);
        $cover->status = 'DELETED';
        $cover->save();

        return $cover;
    }
}
