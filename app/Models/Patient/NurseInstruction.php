<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseInstruction extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'instruction',
        'created_by'
    ];
}
