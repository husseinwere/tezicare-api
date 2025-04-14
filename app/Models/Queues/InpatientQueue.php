<?php

namespace App\Models\Queues;

use App\Models\Patient\PatientSession;
use App\Models\User;
use App\Models\Ward\Bed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class InpatientQueue extends Model
{
    use HasFactory;

    protected $table = 'inpatients_queue';

    protected $fillable = [
        'hospital_id',
        'inpatient_number',
        'session_id',
        'bed_id',
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
                $latestScopedId = self::where('hospital_id', $hospitalId)->max('inpatient_number') ?? 0;
                $model->scoped_id = $latestScopedId + 1;

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

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function session()
    {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
