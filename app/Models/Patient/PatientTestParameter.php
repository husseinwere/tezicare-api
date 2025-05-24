<?php

namespace App\Models\Patient;

use App\Models\Lab\LabResult;
use App\Models\Lab\LabTestParameter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientTestParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_result_id',
        'lab_test_parameter_id',
        'value'
    ];

    public function lab_result()
    {
        return $this->belongsTo(LabResult::class);
    }

    public function lab_test_parameter()
    {
        return $this->belongsTo(LabTestParameter::class);
    }
}
