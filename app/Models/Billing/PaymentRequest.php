<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'source',
        'amount',
        'items',
        'created_by',
        'status'
    ];
}
