<?php

namespace App\Models\Lab;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'lab',
        'test',
        'price',
        'created_by',
        'status'
    ];

    public function prices()
    {
        return $this->hasMany(LabTestPrice::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
