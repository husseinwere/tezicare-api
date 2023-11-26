<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'patient_type',
        'consultation_type',
        'registration_fee',
        'consultation_fee',
        'doctor_id',
        'discharged',
        'created_by',
        'status'
    ];
}
