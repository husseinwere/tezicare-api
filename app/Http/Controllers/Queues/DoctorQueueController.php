<?php

namespace App\Http\Controllers\Queues;

use App\Models\Queues\DoctorQueue;

class DoctorQueueController extends QueueBaseController
{
    public function __construct(DoctorQueue $model)
    {
        parent::__construct($model);
    }
}
