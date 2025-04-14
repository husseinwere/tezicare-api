<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Hospital;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class HospitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $name = $request->query('name');

        $query = Hospital::whereIn('status', ['ACTIVE', 'INACTIVE']);

        if($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        return $query->latest()->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function show(string $id)
    {
        return Hospital::find($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'registration_fee' => 'required'
        ]);
        $data = $request->all();

        if($request->hasFile('logo_file')) {
            $image = $request->file('logo_file');
            $path = $image->store('images/logos', 'public');
            $imageUrl = asset('storage/' . $path);
            $data['logo'] = $imageUrl;
        }

        if($request->hasFile('stamp_file')) {
            $image = $request->file('stamp_file');
            $path = $image->store('images/stamps', 'public');
            $imageUrl = asset('storage/' . $path);
            $data['stamp'] = $imageUrl;
        }

        $createdHospital = Hospital::create($data);

        if($createdHospital){
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

        $hospital = Hospital::find($id);

        if($request->hasFile('logo_file')) {
            $oldImage = $hospital->logo;
            if($oldImage) {
                $splitPath = explode('/', $oldImage);
                $imageName = end($splitPath);
                Storage::delete('public/images/logos/' . $imageName);
            }

            $image = $request->file('logo_file');
            $path = $image->store('images/logos', 'public');
            $imageUrl = asset('storage/' . $path);
            $data['logo'] = $imageUrl;
        }

        if($request->hasFile('stamp_file')) {
            $oldImage = $hospital->stamp;
            if($oldImage) {
                $splitPath = explode('/', $oldImage);
                $imageName = end($splitPath);
                Storage::delete('public/images/stamps/' . $imageName);
            }

            $image = $request->file('stamp_file');
            $path = $image->store('images/stamps', 'public');
            $imageUrl = asset('storage/' . $path);
            $data['stamp'] = $imageUrl;
        }

        $updatedHospital = $hospital->update($data);

        if($updatedHospital){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
