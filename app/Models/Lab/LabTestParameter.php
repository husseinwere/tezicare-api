<?php

namespace App\Models\Lab;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_test_id',
        'name',
        'unit',
        'normal_range',
        'status'
    ];

    public function lab_test()
    {
        return $this->belongsTo(LabTest::class);
    }
}
