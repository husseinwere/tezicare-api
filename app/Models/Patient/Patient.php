<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'national_id',
        'phone',
        'email',
        'residence',
        'created_by',
        'status'
    ];
}
