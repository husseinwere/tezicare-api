<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\LabQueue;

class LabQueueController extends QueueBaseController
{
    public function __construct(LabQueue $model)
    {
        parent::__construct($model);
    }
}
