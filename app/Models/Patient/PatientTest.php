<?php

namespace App\Models\Patient;

use App\Models\Lab\LabResult;
use App\Models\Lab\LabTest;
use App\Models\User;
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

    public function lab_test() {
        return $this->belongsTo(LabTest::class, 'test_id');
    }

    public function lab_result(): HasOne {
        return $this->hasOne(LabResult::class);
    }

    public function created_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
