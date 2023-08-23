<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientImpression extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'impression',
        'created_by'
    ];
}
