<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'logo',
        'stamp',
        'registration_fee',
        'modules',
        'rate_per_visit'
    ];
}
