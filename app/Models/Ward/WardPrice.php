<?php

namespace App\Models\Ward;

use App\Models\Hospital\InsuranceCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
        'insurance_id',
        'price'
    ];

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
