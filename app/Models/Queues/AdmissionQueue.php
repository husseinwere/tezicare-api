<?php

namespace App\Models\Queues;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionQueue extends Model
{
    use HasFactory;

    protected $table = 'admission_queue';

    protected $fillable = [
        'session_id',
        'created_by',
        'status'
    ];
}
