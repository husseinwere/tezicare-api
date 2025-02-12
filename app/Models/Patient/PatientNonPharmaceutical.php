<?php

namespace App\Models\Patient;

use App\Models\Inventory\NonPharmaceutical;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientNonPharmaceutical extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'non_pharmaceutical_id',
        'quantity',
        'unit_price',
        'created_by',
        'payment_status',
        'status'
    ];

    public function non_pharmaceutical(): BelongsTo {
        return $this->belongsTo(NonPharmaceutical::class);
    }

    public function created_by(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
}
