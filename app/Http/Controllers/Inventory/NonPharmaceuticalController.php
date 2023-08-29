<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Inventory\NonPharmaceutical;

class NonPharmaceuticalController extends InventoryBaseController
{
    public function __construct(NonPharmaceutical $model)
    {
        parent::__construct($model);
    }
}
