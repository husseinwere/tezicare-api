<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientRadiologyTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'test_id',
        'test',
        'price',
        'created_by',
        'payment_status',
        'result',
        'description',
        'tested_by',
        'status'
    ];
}
