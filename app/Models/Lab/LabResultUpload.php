<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabResultUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'result_id',
        'url'
    ];
}
