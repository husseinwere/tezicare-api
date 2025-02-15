<?php

namespace App\Models\Patient;

use App\Models\Hospital\InsuranceCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientInsurance extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'insurance_id',
        'card_no',
        'cap',
        'created_by',
        'status'
    ];

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
