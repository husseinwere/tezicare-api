<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabTest;
use App\Models\Lab\LabTestPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $hospital_id = Auth::user()->hospital_id;
        $lab = ucfirst($request->query('lab'));
        
        $query = LabTest::with(['prices'])->where('hospital_id', $hospital_id)->where('status', 'ACTIVE');

        if($lab) {
            $query->where('lab', $lab);
        }

        return $query->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lab' => 'required',
            'test' => 'required',
            'price' => 'required'
        ]);
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $createdTest = LabTest::create($data);

        if($createdTest){
            foreach ($data['prices'] as $insuranceId => $price) {
                if($price) {
                    LabTestPrice::create([
                        'lab_test_id' => $createdTest->id,
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

        $test = LabTest::find($id);
        $updatedTest = $test->update($data);

        if($updatedTest){
            foreach ($data['prices'] as $insuranceId => $price) {
                $existingPrice = LabTestPrice::where('lab_test_id', $test->id)->where('insurance_id', $insuranceId)->first();
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
                        LabTestPrice::create([
                            'lab_test_id' => $updatedTest->id,
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
        $test = LabTest::find($id);
        $test->status = 'DELETED';

        if($test->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
