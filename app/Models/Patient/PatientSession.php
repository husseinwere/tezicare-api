<?php

namespace App\Models\Patient;

use App\Models\Hospital\ConsultationType;
use App\Models\Hospital\Hospital;
use App\Models\Hospital\InsuranceCover;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

class PatientSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'invoice_number',
        'patient_id',
        'patient_type',
        'consultation_type',
        'registration_fee',
        'consultation_fee',
        'primary_payment_method',
        'insurance_id',
        'doctor_id',
        'discharged',
        'created_by',
        'status',
        'created_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $hospitalId = $model->hospital_id;

            // Try assigning a scoped_id with up to 3 retries
            $attempts = 0;
            $maxAttempts = 3;

            do {
                $attempts++;

                // Get the latest scoped_id for this hospital
                $latestScopedId = self::where('hospital_id', $hospitalId)->max('invoice_number') ?? 0;
                $model->invoice_number = $latestScopedId + 1;

                try {
                    // Try saving manually here so we catch duplication early
                    $model->saveQuietly();
                    return false; // Prevent Laravel from saving again
                } catch (QueryException $e) {
                    if ($attempts >= $maxAttempts) {
                        throw $e; // Rethrow after max attempts
                    }
                    // Optional: check if the error is actually a unique constraint violation
                    usleep(100000); // sleep 100ms before retry
                }

            } while ($attempts < $maxAttempts);
        });
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(ConsultationType::class, 'consultation_type');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
