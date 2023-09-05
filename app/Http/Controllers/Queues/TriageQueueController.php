<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\TriageQueue;

class TriageQueueController extends QueueBaseController
{
    public function __construct(TriageQueue $model)
    {
        parent::__construct($model);
    }
}
