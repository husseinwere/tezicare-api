<?php

namespace App\Models\Ward;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'name',
        'description',
        'price',
        'created_by',
        'status'
    ];
}
