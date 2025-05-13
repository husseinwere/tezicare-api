<?php

namespace App\Http\Controllers\Dental;

use App\Http\Controllers\Controller;
use App\Models\Dental\DentalService;
use App\Models\Dental\DentalServicePrice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DentalServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $hospital_id = Auth::user()->hospital_id;
        $name = $request->query('name');

        $query = DentalService::with(['prices'])->where('hospital_id', $hospital_id)->where('status', 'ACTIVE');

        if($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        return $query->paginate($pageSize, ['*'], 'page', $pageIndex);
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

        $createdService = DentalService::create($data);

        if($createdService){
            foreach ($data['prices'] as $insuranceId => $price) {
                if($price) {
                    DentalServicePrice::create([
                        'dental_service_id' => $createdService->id,
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

        $service = DentalService::find($id);
        $updatedService = $service->update($data);

        if($updatedService){
            foreach ($data['prices'] as $insuranceId => $price) {
                $existingPrice = DentalServicePrice::where('dental_service_id', $service->id)->where('insurance_id', $insuranceId)->first();
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
                        DentalServicePrice::create([
                            'dental_service_id' => $service->id,
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
        $service = DentalService::find($id);
        $service->status = 'DELETED';

        if($service->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
