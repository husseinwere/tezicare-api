<?php

namespace App\Models\Lab;

use App\Models\Hospital\InsuranceCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_test_id',
        'insurance_id',
        'price'
    ];

    public function lab_test()
    {
        return $this->belongsTo(LabTest::class);
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
