<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Inventory\NonPharmaceutical;
use App\Models\Inventory\NonPharmaceuticalPrice;

class NonPharmaceuticalController extends InventoryBaseController
{
    public function __construct(NonPharmaceutical $model, NonPharmaceuticalPrice $pricesModel)
    {
        parent::__construct($model, $pricesModel);
    }
}
