<?php

namespace App\Models\Patient;

use App\Models\Hospital\ConsultationType;
use App\Models\Hospital\Hospital;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
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

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ConsultationType::class, 'consultation_type');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
