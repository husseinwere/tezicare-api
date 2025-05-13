<?php

namespace App\Models\Dental;

use App\Models\Hospital\InsuranceCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalServicePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_service_id',
        'insurance_id',
        'price'
    ];

    public function dental_service()
    {
        return $this->belongsTo(DentalService::class);
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
