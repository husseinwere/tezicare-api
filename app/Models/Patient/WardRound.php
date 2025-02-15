<?php

namespace App\Models\Patient;

use App\Models\User;
use App\Models\Ward\Bed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'bed_id',
        'bed_price',
        'doctor_id',
        'doctor_notes',
        'doctor_price',
        'nurse_id',
        'nurse_notes',
        'nurse_price'
    ];

    public function session(): BelongsTo {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function bed(): BelongsTo {
        return $this->belongsTo(Bed::class);
    }

    public function doctor(): BelongsTo {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function nurse(): BelongsTo {
        return $this->belongsTo(User::class, 'nurse_id');
    }
}
