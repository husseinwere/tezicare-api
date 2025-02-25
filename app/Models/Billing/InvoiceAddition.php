<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAddition extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'category',
        'name',
        'quantity',
        'rate',
        'created_by',
        'status'
    ];

    public function created_by(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
}
