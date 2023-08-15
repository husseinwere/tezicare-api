<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCover extends Model
{
    use HasFactory;
    protected $fillable = [
        'insurance',
        'cap',
        'created_by',
        'status'
    ];
}
