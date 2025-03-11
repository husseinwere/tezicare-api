<?php

namespace App\Models\Patient;

use App\Models\Dental\DentalService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDentalService extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'service_id',
        'price',
        'created_by',
        'created_by',
        'payment_status',
        'status'
    ];

    public function dental_service(): BelongsTo
    {
        return $this->belongsTo(DentalService::class, 'service_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
