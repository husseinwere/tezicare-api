<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\ClearanceQueue;

class ClearanceQueueController extends QueueBaseController
{
    public function __construct(ClearanceQueue $model)
    {
        parent::__construct($model);
    }
}
