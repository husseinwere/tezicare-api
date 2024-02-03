<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
