<?php

namespace App\Models\Patient;

use App\Models\User;
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

    public function session()
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
