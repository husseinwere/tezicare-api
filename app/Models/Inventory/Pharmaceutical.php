<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmaceutical extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'name',
        'price',
        'quantity',
        'created_by',
        'status'
    ];
}
