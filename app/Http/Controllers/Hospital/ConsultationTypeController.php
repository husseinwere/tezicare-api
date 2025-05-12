<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\ConsultationPrice;
use App\Models\Hospital\ConsultationType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ConsultationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hospital_id = Auth::user()->hospital_id;

        return ConsultationType::with('prices')->where('hospital_id', $hospital_id)->where('status', 'ACTIVE')->get();
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

        $createdType = ConsultationType::create($data);

        if($createdType){
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

        $type = ConsultationType::find($id);
        $updatedType = $type->update($data);

        if($updatedType){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function savePrices(Request $request, string $id)
    {
        $data = $request->all();

        $type = ConsultationType::find($id);

        foreach ($data['prices'] as $insuranceId => $price) {
            $existingPrice = ConsultationPrice::where('consultation_id', $type->id)->where('insurance_id', $insuranceId)->first();
            if ($existingPrice) {
                if($price) {
                    $existingPrice[$data['field']] = $price;
                }
                else {
                    $existingPrice[$data['field']] = null;
                }

                $existingPrice->save();
            }
            else {
                if($price) {
                    ConsultationPrice::create([
                        'consultation_id' => $type->id,
                        'insurance_id' => $insuranceId,
                        $data['field'] => $price
                    ]);
                }
            }
        }

        return response(null, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $type = ConsultationType::find($id);

        if($type->can_delete == 0){
            return response(['message' => 'This consultation type cannot be deleted'], Response::HTTP_FORBIDDEN);
        }
        else {
            $type->status = "DELETED";

            if($type->save()) {
                return response(null, Response::HTTP_NO_CONTENT);
            }
            else {
                return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
