<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'recommendation',
        'created_by'
    ];
}
