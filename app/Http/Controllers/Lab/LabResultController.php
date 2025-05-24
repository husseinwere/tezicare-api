<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabResult;
use App\Models\Lab\LabResultUpload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LabResultController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_test_id' => 'required',
            'result' => 'required',
            'files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);
        $data = $request->all();
        $data['created_by'] = Auth::id();

        $createdResult = LabResult::create($data);        

        if($createdResult){
            // if($request->hasFile('files')) {
            //     foreach($request->file('files') as $file) {
            //         $path = $file->store('public/lab-results');
            //         $url = asset(str_replace('public', 'storage', $path));

            //         $fileUpload = [
            //             'result_id' => $createdResult->id,
            //             'url' => $url
            //         ];
                    
            //         LabResultUpload::create($fileUpload);
            //     }
            // }

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

        $result = LabResult::find($id);
        $updatedResult = $result->update($data);

        if($updatedResult){
            // if($request->hasFile('files')) {
            //     foreach($request->file('files') as $file) {
            //         $path = $file->store('public/lab-results');
            //         $url = asset(str_replace('public', 'storage', $path));

            //         $fileUpload = [
            //             'result_id' => $id,
            //             'url' => $url
            //         ];
                    
            //         LabResultUpload::create($fileUpload);
            //     }
            // }

            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
