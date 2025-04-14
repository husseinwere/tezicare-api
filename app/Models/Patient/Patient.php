<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'outpatient_number',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'national_id',
        'phone',
        'email',
        'residence',
        'created_by',
        'status'
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
                $latestScopedId = self::where('hospital_id', $hospitalId)->max('outpatient_number') ?? 0;
                $model->outpatient_number = $latestScopedId + 1;

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
}
