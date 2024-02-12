<?php

namespace App\Models\Patient;

use App\Models\Lab\LabResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PatientTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'test_id',
        'test',
        'price',
        'additional_info',
        'created_by',
        'payment_status',
        'status'
    ];

    public function results(): HasOne {
        return $this->hasOne(LabResult::class, 'test_id');
    }
}
