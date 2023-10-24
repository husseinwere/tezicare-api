<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'test_id',
        'test',
        'price',
        'additional_info',
        'created_by',
        'payment_status',
        'status'
    ];
}
