<?php

namespace App\Models\Inventory;

use App\Models\Hospital\InsuranceCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmaceuticalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmaceutical_id',
        'insurance_id',
        'price'
    ];

    public function pharmaceutical()
    {
        return $this->belongsTo(Pharmaceutical::class);
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
