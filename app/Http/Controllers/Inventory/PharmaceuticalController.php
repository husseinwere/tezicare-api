<?php

namespace App\Http\Controllers\Inventory;

use App\Models\Inventory\Pharmaceutical;

class PharmaceuticalController extends InventoryBaseController
{
    public function __construct(Pharmaceutical $model)
    {
        parent::__construct($model);
    }
}
