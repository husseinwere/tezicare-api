<?php

namespace App\Models\Patient;

use App\Models\Nurse\NursingService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientNursing extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'service_id',
        'service',
        'price',
        'created_by',
        'payment_status',
        'serviced_by',
        'status'
    ];

    public function nursing_service(): BelongsTo
    {
        return $this->belongsTo(NursingService::class, 'service_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
