<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Pharmaceutical;
use App\Models\Patient\PatientDrug;
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

        $drugs = PatientDrug::join('pharmaceuticals', 'pharmaceuticals.id', '=', 'patient_drugs.drug_id')
                            ->join('users', 'users.id', '=', 'patient_drugs.created_by')
                            ->select('patient_drugs.id', 'patient_drugs.dosage', 'patient_drugs.unit_price', 'patient_drugs.quantity',
                                    'patient_drugs.treatment', 'pharmaceuticals.name as drug', 'patient_drugs.payment_status',
                                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as created_by'))
                            ->where('session_id', $sessionId)->get();

        return $drugs;
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

        $drug = Pharmaceutical::find($data['drug_id']);

        $data['unit_price'] = $drug['price'];
        $data['created_by'] = Auth::id();

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
