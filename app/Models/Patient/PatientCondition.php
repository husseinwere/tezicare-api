<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'condition',
        'created_by'
    ];
}
