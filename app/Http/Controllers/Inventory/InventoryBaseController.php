<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InventoryBaseController extends Controller
{
    protected $model;
    protected $pricesModel;

    public function __construct(Model $model, Model $pricesModel)
    {
        $this->pricesModel = $pricesModel;
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->query('page_size', 20);
        $pageIndex = $request->query('page_index', 1);
        $hospital_id = Auth::user()->hospital_id;
        $search = $request->query('search');
        $shouldPaginate = $request->query('paginate', 'true');

        $query = $this->model::with(['prices'])->where('hospital_id', $hospital_id)->where('status', 'ACTIVE');

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if($shouldPaginate == 'true') {
            return $query->paginate($pageSize, ['*'], 'page', $pageIndex);
        }
        else {
            return $query->get();
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'quantity' => 'required'
        ]);
        $data = $request->all();
        $data['hospital_id'] = Auth::user()->hospital_id;
        $data['created_by'] = Auth::id();

        $createdItem = $this->model::create($data);

        if($createdItem){
            foreach ($data['prices'] as $insuranceId => $price) {
                if($price) {
                    $foreignKey = $this->model::foreignKey();
                    $this->pricesModel::create([
                        $foreignKey => $createdItem->id,
                        'insurance_id' => $insuranceId,
                        'price' => $price
                    ]);
                }
            }

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

        $item = $this->model::find($id);
        $updatedItem = $item->update($data);

        if($updatedItem){
            $foreignKey = $this->model::foreignKey();
            foreach ($data['prices'] as $insuranceId => $price) {
                $existingPrice = $this->pricesModel::where($foreignKey, $item->id)->where('insurance_id', $insuranceId)->first();
                if ($existingPrice) {
                    if($price) {
                        $existingPrice->price = $price;
                        $existingPrice->save();
                    }
                    else {
                        $existingPrice->delete();
                    }
                }
                else {
                    if($price) {
                        $this->pricesModel::create([
                            $foreignKey => $item->id,
                            'insurance_id' => $insuranceId,
                            'price' => $price
                        ]);
                    }
                }
            }

            return response(null, Response::HTTP_OK);
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
        $item = $this->model::find($id);
        $item->status = 'DELETED';

        if($item->save()) {
            return response(null, Response::HTTP_NO_CONTENT);
        }
        else {
            return response(['message' => 'An unexpected error has occurred. Please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
