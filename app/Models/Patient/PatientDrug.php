<?php

namespace App\Models\Patient;

use App\Models\Inventory\Pharmaceutical;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDrug extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'drug_id',
        'dosage',
        'quantity',
        'unit_price',
        'treatment',
        'created_by',
        'payment_status',
        'status'
    ];

    public function pharmaceutical(): BelongsTo {
        return $this->belongsTo(Pharmaceutical::class, 'drug_id');
    }

    public function created_by(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
}
