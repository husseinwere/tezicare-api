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
        'registration_fee',
        'consultation_fee',
        'admission_fee',
        'bed_fee',
        'doctor_fee',
        'nurse_fee',
        'discharged',
        'created_by',
        'status'
    ];
}
