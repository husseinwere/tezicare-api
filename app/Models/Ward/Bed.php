<?php

namespace App\Models\Ward;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
        'name',
        'created_by',
        'status'
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }
}
