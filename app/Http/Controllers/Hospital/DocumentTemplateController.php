<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\Hospital\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        $hospital_id = Auth::user()->hospital_id;

        return DocumentTemplate::where('hospital_id', $hospital_id)->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'html' => 'required',
            'css' => 'required'
        ]);
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;

        $createdTemplate = DocumentTemplate::create($data);

        if($createdTemplate){
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

        $template = DocumentTemplate::find($id);
        $updatedTemplate = $template->update($data);

        if($updatedTemplate){
            return response(null, Response::HTTP_OK);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function renderTemplate(string $id)
    {
        $template = DocumentTemplate::findOrFail($id);
        $html = $template->html;
        $css = $template->css;

        $variables = [];
        foreach ($variables as $key => $value) {
            $html = str_replace("{{ $key }}", $value, $html);
        }

        return response()->json(['html' => "<style>$css</style>" . $html]);
    }
}
