<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient\WardRound;
use App\Models\Ward\Ward;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WardRoundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $sessionId = $request->query('session_id');

        $rounds = WardRound::where('session_id', $sessionId)->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);;
        
        return $rounds;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'bed_id' => 'required',
            'ward_id' => 'required'
        ]);
        $data = $request->all();

        $ward = Ward::find($data['ward_id']);
        $data['bed_price'] = $ward->price;

        $createdRound = WardRound::create($data);

        if($createdRound){
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

        $round = WardRound::find($id);
        $updatedRound = $round->update($data);

        if($updatedRound){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
