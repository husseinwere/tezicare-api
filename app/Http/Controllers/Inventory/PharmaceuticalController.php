<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Inventory\Pharmaceutical;
use App\Models\Inventory\PharmaceuticalPrice;

class PharmaceuticalController extends InventoryBaseController
{
    public function __construct(Pharmaceutical $model, PharmaceuticalPrice $pricesModel)
    {
        parent::__construct($model, $pricesModel);
    }
}
