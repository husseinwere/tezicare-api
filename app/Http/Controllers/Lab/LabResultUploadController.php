<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Lab\LabResultUpload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LabResultUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $result_id = $request->query('result_id');

        return LabResultUpload::where('result_id', $result_id)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'result_id' => 'required',
            'name' => 'required',
            'file' => 'file|mimes:jpg,jpeg,png,pdf|max:10240'
        ]);

        $data = $request->all();

        if($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('public/lab_uploads');
            $data['url'] = asset(str_replace('public', 'storage', $path));
        }

        $createdUpload = LabResultUpload::create($data);

        if($createdUpload){
            return response(null, Response::HTTP_CREATED);
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
        $upload = LabResultUpload::find($id);
        if($upload->url) {
            $filePath = str_replace(asset('storage'), 'public', $upload->url);
            if(file_exists($filePath)) {
                unlink($filePath);
            }
        }

        if(LabResultUpload::destroy($id)) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
