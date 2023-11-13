<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientNonPharmaceutical extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'non_pharmaceutical_id',
        'quantity',
        'unit_price',
        'created_by',
        'payment_status',
        'status'
    ];
}
