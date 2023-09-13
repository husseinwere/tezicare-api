<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientInsurance extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'insurance_id',
        'card_no',
        'cap',
        'created_by',
        'status'
    ];
}
