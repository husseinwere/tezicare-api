<?php

namespace App\Models\Lab;

use App\Models\Patient\PatientTest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_test_id',
        'result',
        'description',
        'created_by'
    ];

    public function patient_test() {
        return $this->belongsTo(PatientTest::class);
    }

    public function lab_result_uploads(): HasMany {
        return $this->hasMany(LabResultUpload::class, 'result_id');
    }

    public function created_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
