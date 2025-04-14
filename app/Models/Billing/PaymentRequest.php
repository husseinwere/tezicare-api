<?php

namespace App\Models\Billing;

use App\Models\Patient\PatientSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'session_id',
        'source',
        'amount',
        'items',
        'created_by',
        'status'
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
