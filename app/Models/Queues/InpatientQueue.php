<?php

namespace App\Models\Queues;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InpatientQueue extends Model
{
    use HasFactory;

    protected $table = 'inpatients_queue';

    protected $fillable = [
        'session_id',
        'ward_id',
        'bed_id',
        'created_by',
        'status'
    ];
}
