<?php

namespace App\Models\Nurse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NursingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'service',
        'price',
        'created_by',
        'status'
    ];

    public function prices()
    {
        return $this->hasMany(NursingServicePrice::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
