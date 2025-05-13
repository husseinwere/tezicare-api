<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\Nurse\NursingService;
use App\Models\Nurse\NursingServicePrice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NursingServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $hospital_id = Auth::user()->hospital_id;
        $service = $request->query('name');

        $query = NursingService::with(['prices'])->where('hospital_id', $hospital_id)->where('status', 'ACTIVE');

        if ($service) {
            $query->where('service', 'LIKE', '%' . $service . '%');
        }

        return $query->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service' => 'required',
            'price' => 'required'
        ]);
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $createdService = NursingService::create($data);

        if($createdService){
            foreach ($data['prices'] as $insuranceId => $price) {
                if($price) {
                    NursingServicePrice::create([
                        'nursing_service_id' => $createdService->id,
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

        $service = NursingService::find($id);
        $updatedService = $service->update($data);

        if($updatedService){
            foreach ($data['prices'] as $insuranceId => $price) {
                $existingPrice = NursingServicePrice::where('nursing_service_id', $service->id)->where('insurance_id', $insuranceId)->first();
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
                        NursingServicePrice::create([
                            'nursing_service_id' => $service->id,
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
        $service = NursingService::find($id);
        $service->status = 'DELETED';

        if($service->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
