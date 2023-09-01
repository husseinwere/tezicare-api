<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\RadiologyQueue;

class RadiologyQueueController extends QueueBaseController
{
    public function __construct(RadiologyQueue $model)
    {
        parent::__construct($model);
    }
}
