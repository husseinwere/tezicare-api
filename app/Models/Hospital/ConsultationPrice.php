<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id',
        'insurance_id',
        'consultation_price',
        'inpatient_doctor_price',
        'inpatient_nurse_price'
    ];

    public function consultation()
    {
        return $this->belongsTo(ConsultationType::class, 'consultation_id');
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
