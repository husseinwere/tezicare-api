<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShaInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_id',
        'rebate_amount',
    ];

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
