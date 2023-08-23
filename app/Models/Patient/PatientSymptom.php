<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientSymptom extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'symptom',
        'created_by'
    ];
}
