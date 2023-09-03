<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'request_id',
        'payment_method',
        'amount',
        'mpesa_code',
        'insurance_id',
        'created_by',
        'status'
    ];
}
