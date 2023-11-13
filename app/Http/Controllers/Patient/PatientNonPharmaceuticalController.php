<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Inventory\NonPharmaceutical;
use App\Models\Patient\PatientNonPharmaceutical;
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

        $nonPharmaceuticals = PatientNonPharmaceutical::join('non_pharmaceuticals', 'non_pharmaceuticals.id', '=', 'patient_non_pharmaceuticals.drug_id')
                            ->join('users', 'users.id', '=', 'patient_non_pharmaceuticals.created_by')
                            ->select('patient_non_pharmaceuticals.id', 'patient_non_pharmaceuticals.unit_price', 'patient_non_pharmaceuticals.quantity',
                                    'non_pharmaceuticals.name as non_pharmaceutical', 'patient_non_pharmaceuticals.payment_status',
                                    DB::raw('CONCAT(users.first_name, " ", users.last_name) as created_by'))
                            ->where('session_id', $sessionId)->get();

        return $nonPharmaceuticals;
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

        $nonPharmaceutical = NonPharmaceutical::find($data['non_pharmaceutical_id']);

        $data['unit_price'] = $nonPharmaceutical['price'];
        $data['created_by'] = Auth::id();

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
