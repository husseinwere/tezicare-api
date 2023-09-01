<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\NurseQueue;

class NurseQueueController extends QueueBaseController
{
    public function __construct(NurseQueue $model)
    {
        parent::__construct($model);
    }
}
