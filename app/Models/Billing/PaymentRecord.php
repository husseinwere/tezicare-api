<?php

namespace App\Models\Billing;

use App\Models\Patient\PatientInsurance;
use App\Models\Patient\PatientSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'session_id',
        'request_id',
        'payment_method',
        'amount',
        'mpesa_code',
        'insurance_id',
        'created_by',
        'status'
    ];

    public function created_by(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function request(): BelongsTo {
        return $this->belongsTo(PaymentRequest::class, 'request_id');
    }

    public function session(): BelongsTo {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function patient_insurance(): BelongsTo {
        return $this->belongsTo(PatientInsurance::class, 'insurance_id');
    }
}
