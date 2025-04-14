<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCover extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'hospital_id',
        'insurance',
        'cap',
        'created_by',
        'status'
    ];
}
