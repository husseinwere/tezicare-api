<?php

namespace App\Models\Patient;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'created_by',
        'discharged',
        'status'
    ];

    public function session(): BelongsTo {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function created_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
