<?php

namespace App\Models\Patient;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardRoundRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_round_id',
        'record_type',
        'description',
        'created_by',
        'status'
    ];

    public function created_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
}
