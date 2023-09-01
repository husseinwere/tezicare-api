<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\PharmacyQueue;

class PharmacyQueueController extends QueueBaseController
{
    public function __construct(PharmacyQueue $model)
    {
        parent::__construct($model);
    }
}
