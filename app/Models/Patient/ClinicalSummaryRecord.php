<?php

namespace App\Models\Patient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicalSummaryRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'summary',
        'created_by'
    ];
}
