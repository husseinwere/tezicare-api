<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Pharmaceutical;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PharmaceuticalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        
        return Pharmaceutical::where('status', 'ACTIVE')->paginate($pageSize, ['*'], 'page', $pageIndex);
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
        $data['quantity'] = 0;
        $data['created_by'] = Auth::id();

        $createdPharmaceutical = Pharmaceutical::create($data);

        if($createdPharmaceutical){
            return response(null, Response::HTTP_CREATED);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->all();

        $pharmaceutical = Pharmaceutical::find($id);
        $updatedPharmaceutical = $pharmaceutical->update($data);

        if($updatedPharmaceutical){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $patient = Pharmaceutical::find($id);
        $patient->status = 'DELETED';

        if($patient->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['error' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search the specified resource by name.
     */
    public function search(Request $request, string $name)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);

        return Pharmaceutical::where('name', 'like', '%' . $name . '%')->paginate($pageSize, ['*'], 'page', $pageIndex);
    }
}
