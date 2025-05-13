<?php

namespace App\Models\Nurse;

use App\Models\Hospital\InsuranceCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NursingServicePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'nursing_service_id',
        'insurance_id',
        'price'
    ];

    public function nursing_service()
    {
        return $this->belongsTo(NursingService::class);
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
