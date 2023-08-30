<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientNursing extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'service_id',
        'service',
        'price',
        'created_by',
        'payment_status',
        'serviced_by',
        'status'
    ];
}
