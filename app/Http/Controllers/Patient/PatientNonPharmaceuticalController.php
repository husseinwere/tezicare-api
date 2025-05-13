<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Inventory\NonPharmaceutical;
use App\Models\Patient\PatientNonPharmaceutical;
use App\Models\Patient\PatientSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PatientNonPharmaceuticalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sessionId = $request->query('session_id');

        return PatientNonPharmaceutical::with(['non_pharmaceutical', 'created_by'])->where('session_id', $sessionId)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'non_pharmaceutical_id' => 'required',
            'quantity' => 'required'
        ]);

        $data = $request->all();

        $session = PatientSession::find($data['session_id']);
        $nonPharmaceutical = NonPharmaceutical::with('prices')->find($data['non_pharmaceutical_id']);

        $data['non_pharmaceutical_name'] = $nonPharmaceutical['name'];
        $data['unit_price'] = $nonPharmaceutical['price'];
        $data['created_by'] = Auth::id();

        $prices = $nonPharmaceutical->prices;
        if($session->insurance_id) {
            $insurancePrice = $prices->where('insurance_id', $session->insurance_id)->first();
            if($insurancePrice) {
                $data['unit_price'] = $insurancePrice['price'];
            }
        }

        if($data['quantity'] > $nonPharmaceutical->quantity) {
            return response(['message' => 'Not enough stock to use this non pharmaceutical item.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            return DB::transaction(function () use ($data, $nonPharmaceutical) {
                $createdNonPharmaceutical = PatientNonPharmaceutical::create($data);
                $dispensedNonPharmaceutical = $nonPharmaceutical->decrement('quantity', $data['quantity']);

                if($createdNonPharmaceutical && $dispensedNonPharmaceutical) {
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
        $nonPharmaceutical = PatientNonPharmaceutical::find($id);
        $stock = NonPharmaceutical::find($nonPharmaceutical['non_pharmaceutical_id']);

        if($nonPharmaceutical['payment_status'] == "PAID" || $nonPharmaceutical['status'] == "CLEARED") {
            return response(['message' => 'You cannot delete paid for or cleared non pharmaceuticals.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else {
            return DB::transaction(function () use ($id, $nonPharmaceutical, $stock) {
                $incrementedStock = $stock->increment('quantity', $nonPharmaceutical['quantity']);
                $deletedNonPharmaceutical = PatientNonPharmaceutical::destroy($id);

                if($incrementedStock && $deletedNonPharmaceutical){
                    return response(null, Response::HTTP_NO_CONTENT);
                }
                else {
                    return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            });
        }
    }
}
