<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'name',
        'price',
        'inpatient_nurse_rate',
        'inpatient_doctor_rate',
        'status'
    ];

    protected $casts = [
        'can_delete' => 'boolean'
    ];

    public function prices()
    {
        return $this->hasMany(ConsultationPrice::class, 'consultation_id');
    }
}
