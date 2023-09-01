<?php

namespace App\Models\Ward;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
        'name',
        'created_by',
        'status'
    ];
}
