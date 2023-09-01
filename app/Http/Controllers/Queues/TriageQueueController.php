<?php

namespace App\Http\Controllers\Queues;

use App\Http\Controllers\Controller;
use App\Models\Queues\TriageQueue;

class TriageQueueController extends Controller
{
    public function __construct(TriageQueue $model)
    {
        parent::__construct($model);
    }
}
