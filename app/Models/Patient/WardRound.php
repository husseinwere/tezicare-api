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
        'hospital_id',
        'session_id',
        'bed_id',
        'bed_price',
        'doctor_price',
        'nurse_price',
        'created_by',
        'created_at'
    ];

    public function session(): BelongsTo {
        return $this->belongsTo(PatientSession::class, 'session_id');
    }

    public function bed(): BelongsTo {
        return $this->belongsTo(Bed::class);
    }

    public function records() {
        return $this->hasMany(WardRoundRecord::class);
    }

    public function created_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
