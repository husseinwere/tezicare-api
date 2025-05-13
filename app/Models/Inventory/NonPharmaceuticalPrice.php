<?php

namespace App\Models\Inventory;

use App\Models\Hospital\InsuranceCover;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonPharmaceuticalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'non_pharmaceutical_id',
        'insurance_id',
        'price'
    ];

    public function non_pharmaceutical()
    {
        return $this->belongsTo(NonPharmaceutical::class);
    }

    public function insurance()
    {
        return $this->belongsTo(InsuranceCover::class, 'insurance_id');
    }
}
