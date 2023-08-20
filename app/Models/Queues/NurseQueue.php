<?php

namespace App\Models\Queues;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'created_by',
        'status'
    ];
}
