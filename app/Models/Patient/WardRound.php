<?php

namespace App\Models\Patient;

use App\Models\Ward\Ward;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'ward_id',
        'bed_id',
        'bed_price',
        'doctor_id',
        'doctor_comment',
        'doctor_price',
        'nurse_id',
        'nurse_comment',
        'nurse_price'
    ];

    public function ward(): BelongsTo {
        return $this->belongsTo(Ward::class);
    }
}
