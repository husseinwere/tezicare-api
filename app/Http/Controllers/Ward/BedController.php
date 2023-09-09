<?php

namespace App\Http\Controllers\Ward;

use App\Http\Controllers\Controller;
use App\Models\Ward\Bed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $wardId = $request->query('ward_id');

        $beds = Bed::where('ward_id', $wardId)->get();
        
        return $beds;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ward_id' => 'required',
            'name' => 'required'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdBed = Bed::create($data);

        if($createdBed){
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

        $bed = Bed::find($id);
        $updatedBed = $bed->update($data);

        if($updatedBed){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function transfer(Request $request, string $id)
    {
        $request->validate([
            'ward_id' => 'required'
        ]);
        $data = $request->all();

        $bed = Bed::find($id);

        if($bed['status'] == "UNOCCUPIED"){
            $updatedBed = $bed->update($data);

            if($updatedBed){
                return response(null, Response::HTTP_OK);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'You cannot transfer a bed that is occupied.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bed = Bed::find($id);

        if($bed['status'] == "UNOCCUPIED"){
            if(Bed::destroy($id)) {
                return response(null, Response::HTTP_NO_CONTENT);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else {
            return response(['message' => 'You cannot delete a bed that is occupied.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
