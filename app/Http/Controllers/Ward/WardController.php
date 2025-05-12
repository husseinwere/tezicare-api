<?php

namespace App\Http\Controllers\Ward;

use App\Http\Controllers\Controller;
use App\Models\Ward\Ward;
use App\Models\Ward\WardPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class WardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hospital_id = Auth::user()->hospital_id;

        return Ward::with('prices')->where('hospital_id', $hospital_id)->where('status', 'ACTIVE')->get();
    }

    public function show(string $id)
    {
        return Ward::with('prices')->find($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required'
        ]);
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $createdWard = Ward::create($data);

        if($createdWard){
            foreach ($data['prices'] as $insuranceId => $price) {
                if($price) {
                    WardPrice::create([
                        'ward_id' => $createdWard->id,
                        'insurance_id' => $insuranceId,
                        'price' => $price
                    ]);
                }
            }

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

        $ward = Ward::find($id);
        $updatedWard = $ward->update($data);

        if($updatedWard){
            foreach ($data['prices'] as $insuranceId => $price) {
                $existingPrice = WardPrice::where('ward_id', $ward->id)->where('insurance_id', $insuranceId)->first();
                if ($existingPrice) {
                    if($price) {
                        $existingPrice->price = $price;
                        $existingPrice->save();
                    }
                    else {
                        $existingPrice->delete();
                    }
                }
                else {
                    if($price) {
                        WardPrice::create([
                            'ward_id' => $ward->id,
                            'insurance_id' => $insuranceId,
                            'price' => $price
                        ]);
                    }
                }
            }

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
        $ward = Ward::find($id);

        $ward->status = "DELETED";

        if($ward->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
