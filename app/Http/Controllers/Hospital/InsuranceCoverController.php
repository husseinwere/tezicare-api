<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\InsuranceCover;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InsuranceCoverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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

        $createdCover = InsuranceCover::create($data);

        if($createdCover){
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
        $request->validate([
            'insurance' => 'required'
        ]);
        $data = $request->all();

        $cover = InsuranceCover::find($id);
        $updatedCover = $cover->update($data);

        if($updatedCover){
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
        $cover = InsuranceCover::find($id);
        $cover->status = 'DELETED';
        if($cover->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
