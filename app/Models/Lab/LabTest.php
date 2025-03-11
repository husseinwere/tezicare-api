<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab',
        'test',
        'price',
        'created_by',
        'status'
    ];
}
