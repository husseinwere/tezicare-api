<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDrug extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'drug_id',
        'dosage',
        'quantity',
        'unit_price',
        'treatment',
        'created_by',
        'payment_status',
        'status'
    ];
}
