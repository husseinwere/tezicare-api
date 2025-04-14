<?php

namespace App\Models\Appointment;

use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'patient_id',
        'appointment_date',
        'duration',
        'description',
        'created_by',
        'status'
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
