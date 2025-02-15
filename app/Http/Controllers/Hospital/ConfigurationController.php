<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\Configuration;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Configuration::first();
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

        $createdConfig = Configuration::create($data);

        if($createdConfig){
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

        $config = Configuration::find($id);

        if($request->hasFile('logo_file')) {
            $oldImage = $config->logo;
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
            $oldImage = $config->stamp;
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

        $updatedConfig = $config->update($data);

        if($updatedConfig){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
