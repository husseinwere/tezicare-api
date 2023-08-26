<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiologyTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'test',
        'price',
        'created_by'
    ];
}
