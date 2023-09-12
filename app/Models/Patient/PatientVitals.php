<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVitals extends Model
{
    use HasFactory;

    protected $table = "patient_vitals";

    protected $fillable = [
        'session_id',
        'temperature',
        'height',
        'weight',
        'blood_pressure',
        'spo2',
        'pulse_rate',
        'created_by'
    ];
}
