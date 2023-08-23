<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDiagnosis extends Model
{
    use HasFactory;

    protected $table = 'patient_diagnosis';

    protected $fillable = [
        'session_id',
        'diagnosis',
        'created_by'
    ];
}
