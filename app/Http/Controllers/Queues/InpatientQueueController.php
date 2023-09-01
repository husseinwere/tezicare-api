<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\InpatientQueue;

class InpatientQueueController extends QueueBaseController
{
    public function __construct(InpatientQueue $model)
    {
        parent::__construct($model);
    }
}
