<?php

namespace App\Models\Nurse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NursingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'service',
        'price',
        'created_by',
        'status'
    ];
}
