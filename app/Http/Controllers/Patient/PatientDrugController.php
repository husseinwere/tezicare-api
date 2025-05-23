<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Pharmaceutical;
use App\Models\Patient\PatientDrug;
use App\Models\Patient\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientDrugController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        return PatientDrug::with(['pharmaceutical', 'created_by'])->where('session_id', $sessionId)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'drug_id' => 'required',
            'dosage' => 'required',
            'quantity' => 'required'
        ]);

        $data = $request->all();

        $session = PatientSession::find($data['session_id']);
        $drug = Pharmaceutical::with('prices')->find($data['drug_id']);

        $data['drug_name'] = $drug['name'];
        $data['unit_price'] = $drug['price'];
        $data['created_by'] = Auth::id();

        $prices = $drug->prices;
        if($session->insurance_id) {
            $insurancePrice = $prices->where('insurance_id', $session->insurance_id)->first();
            if($insurancePrice) {
                $data['unit_price'] = $insurancePrice['price'];
            }
        }

        if($data['quantity'] > $drug->quantity) {
            return response(['message' => 'Not enough stock to dispense this drug.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            return DB::transaction(function () use ($data, $drug) {
                $createdDrug = PatientDrug::create($data);
                $dispensedDrug = $drug->decrement('quantity', $data['quantity']);

                if($createdDrug && $dispensedDrug) {
                    return response(null, Response::HTTP_CREATED);
                }
                else {
                    return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            });
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $drug = PatientDrug::find($id);
        $stock = Pharmaceutical::find($drug['drug_id']);

        if($drug['payment_status'] == "PAID" || $drug['status'] == "CLEARED") {
            return response(['message' => 'You cannot delete paid for or cleared drugs.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            return DB::transaction(function () use ($id, $drug, $stock) {
                $incrementedStock = $stock->increment('quantity', $drug['quantity']);
                $deletedDrug = PatientDrug::destroy($id);

                if($incrementedStock && $deletedDrug){
                    return response(null, Response::HTTP_NO_CONTENT);
                }
                else {
                    return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            });
        }
    }
}
