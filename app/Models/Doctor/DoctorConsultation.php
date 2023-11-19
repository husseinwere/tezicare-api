<?php

namespace App\Models\Doctor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'doctor_id',
        'price'
    ];
}
