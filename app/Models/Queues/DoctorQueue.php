<?php

namespace App\Models\Queues;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorQueue extends Model
{
    use HasFactory;

    protected $table = 'doctor_queue';

    protected $fillable = [
        'session_id',
        'created_by',
        'status'
    ];
}
